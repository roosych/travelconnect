<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id()->startingValue(1001);
            $table->string('public_code')->unique();
            $table->foreignId('agency_id')->constrained('users');
            $table->string('title');
            $table->string('destination')->nullable();
            $table->date('travel_date_from')->nullable();
            $table->date('travel_date_to')->nullable();
            $table->timestampTz('deadline_at')->nullable();
            $table->unsignedInteger('pax_count')->default(0);
            $table->jsonb('services_needed')->nullable(); // jsonb: нужен @> для whereJsonContains
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('pax_count_changed_at')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
