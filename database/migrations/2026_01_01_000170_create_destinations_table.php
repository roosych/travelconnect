<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2);   // место принадлежит стране-направлению
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->cascadeOnDelete();
            $table->unique(['country_code', 'name']);
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
