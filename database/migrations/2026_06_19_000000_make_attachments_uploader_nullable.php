<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Внешние загрузки (поставщик по token-ссылке без аккаунта) не имеют
    // внутреннего юзера → uploader_id должен допускать NULL.
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('uploader_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('uploader_id')->nullable(false)->change();
        });
    }
};
