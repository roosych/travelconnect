<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();

            // Trace links back to the source offer/supplier. Nullable + nullOnDelete:
            // the snapshot must survive even if the offer or supplier is later removed.
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Denormalized supplier name, frozen at accept time (suppliers are soft-deletable / renameable).
            $table->string('supplier_name');

            $table->string('service_type')->nullable();
            $table->string('name');
            $table->text('description')->nullable();

            // Pinned to 1 by business rule: suppliers quote a price for the whole request.
            $table->unsignedInteger('quantity')->default(1);

            // Cost of goods (net), as quoted by the supplier.
            $table->decimal('net_unit_price', 12, 2)->nullable();
            $table->char('net_currency', 3)->nullable();
            $table->decimal('net_fx_rate', 12, 6)->nullable();
            $table->decimal('net_amount_azn', 12, 2);

            // Applied markup and resulting sell price, both frozen in AZN.
            $table->decimal('markup_pct', 5, 2)->default(0);
            $table->decimal('sell_amount_azn', 12, 2);

            $table->timestamps();

            $table->index('booking_id');
            $table->index('supplier_id');
            $table->index('service_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
