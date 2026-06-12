<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->char('country', 2)->nullable();
            $table->char('currency_code', 3)->default('AZN');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('agency_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'manager', 'staff'])->default('staff');
            $table->timestamps();

            $table->unique(['agency_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_users');
        Schema::dropIfExists('agencies');
    }
};
