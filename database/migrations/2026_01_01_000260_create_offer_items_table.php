<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();

            // Optional reference to supplier's catalog; null if ad-hoc item
            $table->foreignId('supplier_service_id')
                ->nullable()
                ->constrained('supplier_services')
                ->nullOnDelete();

            $table->string('type');                     // ServiceType enum
            $table->string('name')->nullable();         // «Toyota Hiace 15-seat × 2»; необязательно
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('unit_price_azn', 12, 2)->nullable();  // AZN-эквивалент на момент подачи оффера
            $table->decimal('exchange_rate', 12, 6)->nullable();   // AZN за 1 ед. currency
            $table->char('currency', 3);
            $table->string('price_unit')->default('per_person'); // PriceUnit enum
            $table->timestamps();

            $table->index('offer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_items');
    }
};
