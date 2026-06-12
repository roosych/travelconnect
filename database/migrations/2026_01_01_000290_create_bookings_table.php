<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals');
            $table->foreignId('request_id')->constrained('travel_requests');
            $table->foreignId('agency_id')->constrained('users');
            $table->foreignId('operator_id')->constrained('users');
            $table->timestamp('confirmed_at');
            $table->date('travel_date_from');
            $table->date('travel_date_to');
            $table->unsignedInteger('pax_count');
            $table->decimal('final_price', 10, 2);
            // Снимок себестоимости (роллапы), всё в AZN — стабильной рабочей валюте.
            $table->decimal('cost_total_azn', 12, 2)->nullable();
            $table->decimal('sell_total_azn', 12, 2)->nullable();
            $table->decimal('margin_azn', 12, 2)->nullable();
            // Курс AZN → валюта агентства для final_price (1 если агентство биллит в AZN).
            $table->decimal('fx_rate_to_agency', 12, 6)->nullable();
            $table->string('currency', 3);
            $table->string('status')->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('proposal_id');
            $table->index('request_id');
            $table->index('agency_id');
            $table->index('operator_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
