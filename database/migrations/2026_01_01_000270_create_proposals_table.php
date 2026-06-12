<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id()->startingValue(1001);
            $table->foreignId('request_id')->constrained('travel_requests');
            $table->foreignId('operator_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3);
            // Снимок на момент отправки: исходная сумма в рабочей валюте + курс конвертации
            $table->decimal('original_total_price', 12, 2)->nullable();
            $table->char('original_currency', 3)->nullable();
            $table->decimal('exchange_rate_snapshot', 16, 6)->nullable();
            $table->timestamp('valid_until'); // момент с временем (оператор задаёт время)
            $table->string('status')->default('draft');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index('request_id');
            $table->index('operator_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
