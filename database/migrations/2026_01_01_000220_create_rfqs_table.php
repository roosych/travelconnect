<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id()->startingValue(1001);
            $table->foreignId('request_id')->constrained('travel_requests');
            // Сегментное измерение RFQ: страна-сегмент + конкретный leg заявки.
            // RFQ рассылается по паре (сегмент × тип услуги): страна нужна для
            // матчинга поставщиков, leg_id несёт даты/направления/требования сегмента.
            $table->char('country_code', 2)->nullable();
            $table->foreignId('leg_id')->nullable()->constrained('travel_request_legs')->nullOnDelete();
            $table->foreignId('operator_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->string('service_type');
            $table->timestamp('deadline_at'); // момент с временем (выровнен с deadline_at заявки)
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries');
            $table->index('request_id');
            $table->index('operator_id');
            $table->index('status');
            $table->index(['leg_id', 'service_type']); // идемпотентность авторассылки: «уже слали для этой пары?»
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
