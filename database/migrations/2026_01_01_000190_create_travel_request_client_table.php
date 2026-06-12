<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_request_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->boolean('is_lead')->default(false);

            $table->unique(['travel_request_id', 'client_id']);
            $table->index('travel_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_request_client');
    }
};
