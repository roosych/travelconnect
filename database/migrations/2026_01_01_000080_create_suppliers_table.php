<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->char('country', 2)->nullable();
            $table->char('currency_code', 3)->default('AZN');
            $table->jsonb('service_types')->default('[]');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(false);
            // Самопауза поставщика: «Не получать пока никаких запросов». Отдельно от
            // is_active (то — выключатель оператора). На паузе поставщик жёстко
            // исключается из авторассылки и ручного добавления, пока сам не снимет.
            $table->boolean('accepting_requests')->default(true);
            $table->boolean('uses_portal')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('supplier_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'manager', 'staff'])->default('staff');
            $table->timestamps();

            $table->unique(['supplier_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_users');
        Schema::dropIfExists('suppliers');
    }
};
