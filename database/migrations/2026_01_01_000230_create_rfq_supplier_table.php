<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->string('token', 64)->nullable()->unique(); // публичная ссылка для поставщика без портала
            $table->timestamp('token_expires_at')->nullable();
            $table->json('service_types')->nullable();
            $table->text('notes')->nullable();

            $table->unique(['rfq_id', 'supplier_id']);
            $table->index('rfq_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_supplier');
    }
};
