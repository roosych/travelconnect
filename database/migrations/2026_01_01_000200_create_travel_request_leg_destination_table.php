<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Направления (города) выбранные в рамках сегмента.
        Schema::create('travel_request_leg_destination', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leg_id')->constrained('travel_request_legs')->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0); // порядок городов в стране (маршрут)
            $table->timestamps();

            $table->unique(['leg_id', 'destination_id']);
            $table->index('leg_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_request_leg_destination');
    }
};
