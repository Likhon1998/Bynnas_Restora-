<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 4 — Immutable stock ledger.
 *
 * Architectural rule (enforced in InventoryService — pending review):
 * Controllers must never overwrite quantity_on_hand / pivot quantity directly.
 * All stock mutations go through inventory_transactions, then cached qty is derived.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('type', 32); // po_receipt|pos_sale|transfer_in|transfer_out|wastage|adjustment
            $table->decimal('quantity_change', 14, 3); // signed; negative = deduction (base units)
            $table->string('unit', 32)->nullable(); // typically base_unit at write time
            $table->decimal('unit_cost_snapshot', 12, 4)->nullable(); // WAC/cost at transaction time
            $table->nullableMorphs('reference'); // reference_type + reference_id (PO, Order, Wastage, Transfer…)
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
