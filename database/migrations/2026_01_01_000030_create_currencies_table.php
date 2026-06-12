<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->char('code', 3)->primary();
            $table->string('name', 60);
            $table->decimal('rate', 12, 6)->default(1.000000); // AZN per 1 unit
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);   // only AZN = true
            $table->timestamp('rates_updated_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
