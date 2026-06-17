<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Contracts\Settleable;
use App\Domain\Payments\DTO\SettlementTarget;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Payments\Events\PaymentConfirmed;
use App\Domain\Payments\Models\Payment;
use App\Domain\Settings\Services\CurrencyConverter;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PaymentService
{
    public function __construct(private readonly CurrencyConverter $currency) {}

    /**
     * Записать платёж по цели расчёта payable-модели.
     * Платёж может быть в ЛЮБОЙ валюте — нормализуем в базовую (AZN) и ведём
     * расчёт в ней. Валюта контрагента → по курсу-снимку payable (совпадает с
     * котировкой); прочие → по текущему курсу из справочника.
     * Исходящие (оператор→поставщик) подтверждаются сразу; входящие от агентства —
     * только если их вносит оператор (агентство — самозаявка, ждёт подтверждения).
     */
    public function record(
        Settleable $payable,
        PaymentDirection $direction,
        Model $counterparty,
        float $amount,
        string $currency,
        string $paidAt,
        ?string $reference,
        ?string $notes,
        User $recorder,
    ): Payment {
        if ($amount <= 0) {
            throw new BusinessRuleException('Сумма платежа должна быть больше нуля.');
        }

        $target  = $this->resolveTarget($payable, $direction, $counterparty);
        $currency = strtoupper($currency);
        $base     = config('payments.base_currency', 'AZN');

        // Перевод суммы платежа в базовую (AZN).
        if ($currency === strtoupper($target->currency)) {
            $ratio      = $target->baseRatio();        // снимок payable
            $amountBase = round($amount * $ratio, 2);
        } elseif ($currency === strtoupper($base)) {
            $ratio      = 1.0;
            $amountBase = round($amount, 2);
        } else {
            $amountBase = round($this->currency->convert($amount, $currency, $base), 2);
            $ratio      = $amount > 0 ? round($amountBase / $amount, 8) : 1.0;
        }

        // Запрет переплаты: новый платёж ≤ остатка в AZN (по всем не удалённым).
        if (! config('payments.allow_overpayment', false)) {
            $alreadyBase = $this->paymentsFor($payable, $target)->sum(fn (Payment $p) => (float) $p->amount_base);
            if ($amountBase - ($target->amountDueBase - $alreadyBase) > 0.01) {
                throw new BusinessRuleException('Сумма платежа превышает остаток по расчёту.');
            }
        }

        $autoConfirm = $direction === PaymentDirection::Outgoing || $recorder->isOperator();

        /** @var Model $payable */
        $payment = new Payment([
            'direction'         => $direction->value,
            'counterparty_type' => $counterparty->getMorphClass(),
            'counterparty_id'   => $counterparty->getKey(),
            'amount'            => round($amount, 2),
            'currency'          => $currency,
            'amount_base'       => $amountBase,
            'fx_rate'           => $ratio,
            'paid_at'           => $paidAt,
            'reference'         => $reference,
            'notes'             => $notes,
            'recorded_by'       => $recorder->id,
            'confirmed_at'      => $autoConfirm ? Carbon::now() : null,
            'confirmed_by'      => $autoConfirm ? $recorder->id : null,
        ]);
        $payment->payable()->associate($payable);
        $payment->save();

        if ($payment->isConfirmed()) {
            PaymentConfirmed::dispatch($payment);
        }

        return $payment;
    }

    /** Подтверждение входящего платежа оператором. */
    public function confirm(Payment $payment, User $operator): Payment
    {
        if ($payment->isConfirmed()) {
            return $payment;
        }

        $payment->confirmed_at = Carbon::now();
        $payment->confirmed_by = $operator->id;
        $payment->save();

        PaymentConfirmed::dispatch($payment);

        return $payment;
    }

    public function delete(Payment $payment): void
    {
        $payment->delete(); // soft delete — статусы пересчитываются деривативно
    }

    /**
     * Леджер по payable: на каждую цель — должно/подтверждено/заявлено/остаток/статус
     * + список платежей. Статус считается по ПОДТВЕРЖДЁННОЙ сумме.
     *
     * @return Collection<int, array>
     */
    public function ledger(Settleable $payable): Collection
    {
        return $payable->settlementTargets()->map(function (SettlementTarget $target) use ($payable) {
            $payments  = $this->paymentsFor($payable, $target);
            $confirmed = $payments->whereNotNull('confirmed_at');

            // Всё считаем в базовой валюте (AZN) — платежи бывают в разных валютах.
            $paidBase    = (float) $confirmed->sum(fn (Payment $p) => (float) $p->amount_base);
            $pendingBase = (float) $payments->whereNull('confirmed_at')->sum(fn (Payment $p) => (float) $p->amount_base);
            $dueBase     = round($target->amountDueBase, 2);
            $remaining   = max(0, round($dueBase - $paidBase, 2));

            $status = $paidBase <= 0
                ? 'pending'
                : ($paidBase + 0.01 >= $dueBase ? 'settled' : 'partial');

            return [
                'direction'    => $target->direction->value,
                'counterparty' => [
                    'type' => $target->counterparty->getMorphClass(),
                    'id'   => $target->counterparty->getKey(),
                    'name' => $target->label ?? ($target->counterparty->name ?? null),
                ],
                // Основные суммы — в AZN. Валюта контрагента — справочно (ref_*).
                'due'          => $dueBase,
                'paid'         => round($paidBase, 2),
                'pending'      => round($pendingBase, 2),
                'remaining'    => $remaining,
                'status'       => $status,
                'ref_currency' => $target->currency,
                'ref_due'      => round($target->amountDue, 2),
                'payments'     => $payments->sortByDesc('paid_at')->values(),
            ];
        });
    }

    /** Все ли входящие цели payable полностью подтверждены-оплачены. */
    public function incomingSettled(Settleable $payable): bool
    {
        $incoming = $payable->settlementTargets()
            ->where('direction', PaymentDirection::Incoming);

        if ($incoming->isEmpty()) {
            return false;
        }

        return $incoming->every(function (SettlementTarget $t) use ($payable) {
            $paidBase = (float) $this->paymentsFor($payable, $t)
                ->whereNotNull('confirmed_at')
                ->sum(fn (Payment $p) => (float) $p->amount_base);

            return $paidBase + 0.01 >= $t->amountDueBase;
        });
    }

    private function resolveTarget(Settleable $payable, PaymentDirection $direction, Model $counterparty): SettlementTarget
    {
        $target = $payable->settlementTargets()->first(
            fn (SettlementTarget $t) => $t->direction === $direction
                && $t->counterparty->getMorphClass() === $counterparty->getMorphClass()
                && (int) $t->counterparty->getKey() === (int) $counterparty->getKey()
        );

        if (! $target) {
            throw new BusinessRuleException('Расчёт с этим контрагентом по этой брони не предусмотрен.');
        }

        return $target;
    }

    /** @return Collection<int, Payment> */
    private function paymentsFor(Settleable $payable, SettlementTarget $target): Collection
    {
        /** @var Model $payable */
        return Payment::query()
            ->where('payable_type', $payable->getMorphClass())
            ->where('payable_id', $payable->getKey())
            ->where('direction', $target->direction->value)
            ->where('counterparty_type', $target->counterparty->getMorphClass())
            ->where('counterparty_id', $target->counterparty->getKey())
            ->get();
    }
}
