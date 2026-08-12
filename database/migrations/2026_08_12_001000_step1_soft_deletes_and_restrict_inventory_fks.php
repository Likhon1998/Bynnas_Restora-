<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 1 — Soft deletes + non-destructive FK policy.
 *
 * Preserves historical accounting rows by:
 * - soft-deleting master records instead of hard deletes
 * - replacing cascadeOnDelete() with restrictOnDelete() / nullOnDelete()
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wastage_records', function (Blueprint $table) {
            $table->softDeletes();
        });

        // recipe_ingredients: keep recipe/item history — block hard deletes
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->restrictOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->restrictOnDelete();
        });

        // purchase_orders → suppliers: restrict (soft-delete supplier instead)
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
        });

        // purchase_order_items: preserve PO line history
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->restrictOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->restrictOnDelete();
        });

        // stock_transfers: preserve transfer audit trail
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->restrictOnDelete();
        });

        // wastage_records: preserve cost-impact history
        Schema::table('wastage_records', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->restrictOnDelete();
        });

        // inventory_items.supplier_id already nullOnDelete — keep that policy
    }

    public function down(): void
    {
        Schema::table('wastage_records', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
            $table->dropSoftDeletes();
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->dropSoftDeletes();
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->dropForeign(['inventory_item_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
