<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Услуга, нужная в рамках сегмента, + структурированные требования
        // под её тип (отель: звёзды/питание; транспорт: тип/вместимость;
        // гид: языки/пол/лицензия). requirements типизируется по service_type.
        Schema::create('leg_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leg_id')->constrained('travel_request_legs')->cascadeOnDelete();
            $table->string('service_type'); // accommodation | transport | guide | activity | other
            $table->jsonb('requirements')->default('{}');
            $table->timestamps();

            $table->unique(['leg_id', 'service_type']);
            $table->index('leg_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leg_services');
    }
};
