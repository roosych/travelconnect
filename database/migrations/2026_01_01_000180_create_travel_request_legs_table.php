<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сегмент маршрута: одна страна заявки со своими датами, направлениями
        // и услугами. Заявка = упорядоченная последовательность сегментов.
        Schema::create('travel_request_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->char('country_code', 2);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
            $table->index('travel_request_id');
            $table->index(['travel_request_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_request_legs');
    }
};
