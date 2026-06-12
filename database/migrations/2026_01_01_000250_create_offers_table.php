<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id()->startingValue(1001);
            $table->foreignId('rfq_id')->constrained('rfqs');
            $table->foreignId('supplier_id')->constrained('users');
            $table->boolean('is_partial')->default(false);
            $table->jsonb('covered_services');               // jsonb: нужен @> для whereJsonContains
            $table->jsonb('uncovered_services')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable(); // null когда есть offer_items — цена считается из них
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 10, 6)->nullable(); // AZN за 1 ед. currency на момент подачи
            $table->decimal('unit_price_azn', 12, 2)->nullable();
            $table->timestamp('valid_until'); // момент с временем (поставщик задаёт время)
            $table->text('notes')->nullable();
            $table->string('status')->default('received');
            $table->timestamps();

            $table->index('rfq_id');
            $table->index('supplier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
