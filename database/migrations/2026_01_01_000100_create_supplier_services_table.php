<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');                          // ServiceType enum
            $table->string('name');                          // "Toyota Hiace 15-seat", "Standard Room"
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable(); // max pax; null = unlimited
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('price_unit')->nullable();        // PriceUnit enum
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['supplier_id', 'type']);
            $table->index(['supplier_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_services');
    }
};
