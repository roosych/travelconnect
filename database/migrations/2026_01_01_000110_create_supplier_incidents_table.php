<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('type');        // IncidentType enum value
            $table->string('severity');    // IncidentSeverity enum value
            $table->string('subject_type')->nullable(); // polymorphic: 'offer', 'booking', …
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('context')->nullable();       // from_status, rfq_id, etc.
            $table->text('notes')->nullable();         // ручная заметка оператора
            $table->timestamps();

            $table->index(['supplier_id', 'type']);
            $table->index(['supplier_id', 'severity']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_incidents');
    }
};
