<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_shared_attachments', function (Blueprint $table) {
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->primary(['rfq_id', 'attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_shared_attachments');
    }
};
