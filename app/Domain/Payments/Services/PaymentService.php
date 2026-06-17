<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Contracts\Settleable;
use App\Domain\Payments\DTO\SettlementTarget;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Payments\Events\PaymentConfirmed;
use App\Domain\Payments\Models\Payment;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PaymentService
{
    /**
     * Записать платёж по цели расчёта payable-модели.
     * Исходящие (оператор→поставщик) подтверждаются сразу; входящие от агентства —
     * только если их вносит оператор (агентство — самозаявка, ждёт подтверждения).
     */
    public function record(
        Settleable $payable,
        PaymentDirection $direction,
        Model $counterparty,
        float $amount,
        string $paidAt,
        ?string $reference,
        ?string $notes,
        User $recorder,
    ): Payment {
        if ($amount <= 0) {
            throw new BusinessRuleException('Сумма платежа должна быть больше нуля.');
        }

        $target = $this->resolveTarget($payable, $direction, $counterparty);

        // Запрет переплаты: новый платёж ≤ остатка (по всем не удалённым платежам).
        if (! config('payments.allow_overpayment', false)) {
            $alreadyBase = $this->paymentsFor($payable, $target)->sum(fn (Payment $p) => (float) $p->amount_base);
            $newBase = round($amount * $target->baseRatio(), 2);
            if ($newBase - ($target->amountDueBase - $alreadyBase) > 0.01) {
                throw new BusinessRuleException('Сумма платежа превышает остаток по расчёту.');
            }
        }

        $ratio = $target->baseRatio();

        $isOperator = $recorder->isOperator();
        $autoConfirm = $direction === PaymentDirection::Outgoing || $isOperator;

        /** @var Model $payable */
        $payment = new Payment([
            'direction'         => $direction->value,
            'counterparty_type' => $counterparty->getMorphClass(),
            'counterparty_id'   => $counterparty->getKey(),
            'amount'            => round($amount, 2),
            'currency'          => $target->currency,
            'amount_base'       => round($amount * $ratio, 2),
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
            $confirmed = $payments->where('confirmed_at', '!=', null);

            $paid     = (float) $confirmed->sum(fn (Payment $p) => (float) $p->amount);
            $paidBase = (float) $confirmed->sum(fn (Payment $p) => (float) $p->amount_base);
            $pending  = (float) $payments->whereNull('confirmed_at')->sum(fn (Payment $p) => (float) $p->amount);
            $remaining = max(0, round($target->amountDue - $paid, 2));

            $status = $paid <= 0
                ? 'pending'
                : ($paid + 0.01 >= $target->amountDue ? 'settled' : 'partial');

            return [
                'direction'    => $target->direction->value,
                'counterparty' => [
                    'type' => $target->counterparty->getMorphClass(),
                    'id'   => $target->counterparty->getKey(),
                    'name' => $target->label ?? ($target->counterparty->name ?? null),
                ],
                'currency'  => $target->currency,
                'due'       => round($target->amountDue, 2),
                'due_base'  => round($target->amountDueBase, 2),
                'paid'      => round($paid, 2),
                'paid_base' => round($paidBase, 2),
                'pending'   => round($pending, 2),
                'remaining' => $remaining,
                'status'    => $status,
                'payments'  => $payments->sortByDesc('paid_at')->values(),
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
            $paid = (float) $this->paymentsFor($payable, $t)
                ->whereNotNull('confirmed_at')
                ->sum(fn (Payment $p) => (float) $p->amount);

            return $paid + 0.01 >= $t->amountDue;
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
