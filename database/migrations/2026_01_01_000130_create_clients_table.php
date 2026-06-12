<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('users');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('passport_number')->nullable();
            $table->char('nationality', 2)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('agency_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
