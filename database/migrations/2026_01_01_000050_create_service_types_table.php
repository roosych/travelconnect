<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Справочник типов услуг (раньше — enum ServiceType). `code` — стабильный
        // идентификатор, на нём завязан матчинг поставщиков и ключи requirements;
        // менять его нельзя. `name` — дефолтный лейбл (англ), фолбэк когда нет
        // перевода по ключу services.types.{code}. Наценка оператора по умолчанию
        // хранится прямо здесь (default_markup_pct).
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('default_markup_pct', 5, 2)->default(0);   // напр. 15.00 = 15%
            $table->boolean('is_active')->default(true);
            $table->boolean('available_for_requests')->default(true);  // показывать в форме заявки (Прочее = false)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('available_for_requests');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
