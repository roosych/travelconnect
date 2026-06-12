<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category'); // NotificationCategory enum value
            $table->string('channel');  // 'mail' | 'telegram'
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            // One row per (user, category, channel). Absence of a row = channel enabled by default.
            $table->unique(['user_id', 'category', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
