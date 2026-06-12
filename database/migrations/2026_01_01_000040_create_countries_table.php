<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->char('code', 2)->primary();          // ISO 3166-1 alpha-2, matches users.country / suppliers.country
            $table->string('name', 80);
            $table->string('timezone', 64)->nullable();                // IANA, основной пояс страны
            $table->boolean('is_active')->default(false);              // страна-партнёр: доступна при заведении агентств/сапплаеров
            $table->boolean('available_for_requests')->default(false); // страна-направление: доступна в заявках и для сапплаеров
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('available_for_requests');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
