<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('offers');
            $table->text('operator_notes');
            $table->decimal('markup_pct', 5, 2)->default(0);
            $table->json('selected_item_types')->nullable();
            $table->jsonb('item_markups')->nullable();
            // Кураторство материалов оператором: какие файлы поставщика увидит агентство.
            // shared_catalog_media_ids: null = все каталожные фото расшарены (дефолт ВКЛ),
            //   массив = только эти media id.
            // shared_attachment_ids: null/[] = ни одно ручное вложение не расшарено (дефолт ВЫКЛ),
            //   массив = только эти attachment id (картинки и документы).
            $table->jsonb('shared_catalog_media_ids')->nullable();
            $table->jsonb('shared_attachment_ids')->nullable();
            // Валюта и курс агентства, замороженные в момент добавления оффера в предложение
            $table->char('agency_currency_code', 3)->nullable();
            $table->decimal('agency_exchange_rate', 12, 6)->nullable();

            $table->unique(['proposal_id', 'offer_id']);
            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_offer');
    }
};
