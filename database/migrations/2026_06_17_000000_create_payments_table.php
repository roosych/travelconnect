<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('public_code')->unique();

            // К чему относится расчёт (полиморфно: Booking и др. в будущем).
            $table->morphs('payable');

            // Направление: incoming = деньги к оператору (от агентства),
            //              outgoing = деньги от оператора (поставщику).
            $table->string('direction', 16);

            // Контрагент платежа (Agency / Supplier).
            $table->morphs('counterparty');

            // Сумма в валюте контрагента + нормализованная в базовую (AZN) + курс.
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->decimal('amount_base', 14, 2);
            $table->decimal('fx_rate', 18, 8)->default(1);

            $table->date('paid_at');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->constrained('users');

            // Подтверждение оператором (для входящих от агентства). Исходящие —
            // подтверждены сразу при записи (оператор и есть плательщик).
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
