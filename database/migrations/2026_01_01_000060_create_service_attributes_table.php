<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Атрибуты (подкатегории) типа услуги. В матчинге НЕ участвуют — нужны
        // только как текст в заявке. `code` — ключ в leg_services.requirements.
        // input_type: select | multiselect | boolean | number | text | textarea.
        // options — [{value,label}] для select/multiselect (label = дефолт-текст,
        // перевод по ключу services.opts.{type}.{attr}.{value}).
        Schema::create('service_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('input_type');
            $table->jsonb('options')->default('[]');
            $table->jsonb('config')->default('{}');   // placeholder, min, max и т.п.
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_type_id', 'code']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_attributes');
    }
};
