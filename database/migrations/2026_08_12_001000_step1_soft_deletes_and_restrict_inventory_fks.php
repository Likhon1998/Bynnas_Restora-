<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        $this->replaceForeignKey('recipe_ingredients', 'recipe_id', 'id', 'recipes', 'restrict');
        $this->replaceForeignKey('recipe_ingredients', 'inventory_item_id', 'id', 'inventory_items', 'restrict');
        $this->replaceForeignKey('purchase_orders', 'supplier_id', 'id', 'suppliers', 'restrict');
        $this->replaceForeignKey('purchase_order_items', 'purchase_order_id', 'id', 'purchase_orders', 'restrict');
        $this->replaceForeignKey('purchase_order_items', 'inventory_item_id', 'id', 'inventory_items', 'restrict');
        $this->replaceForeignKey('stock_transfers', 'inventory_item_id', 'id', 'inventory_items', 'restrict');
        $this->replaceForeignKey('wastage_records', 'inventory_item_id', 'id', 'inventory_items', 'restrict');
    }

    public function down(): void
    {
        $this->replaceForeignKey('wastage_records', 'inventory_item_id', 'id', 'inventory_items', 'cascade');
        $this->replaceForeignKey('stock_transfers', 'inventory_item_id', 'id', 'inventory_items', 'cascade');
        $this->replaceForeignKey('purchase_order_items', 'purchase_order_id', 'id', 'purchase_orders', 'cascade');
        $this->replaceForeignKey('purchase_order_items', 'inventory_item_id', 'id', 'inventory_items', 'cascade');
        $this->replaceForeignKey('purchase_orders', 'supplier_id', 'id', 'suppliers', 'cascade');
        $this->replaceForeignKey('recipe_ingredients', 'recipe_id', 'id', 'recipes', 'cascade');
        $this->replaceForeignKey('recipe_ingredients', 'inventory_item_id', 'id', 'inventory_items', 'cascade');

        Schema::table('wastage_records', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
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

    private function replaceForeignKey(
        string $table,
        string $column,
        string $references,
        string $on,
        string $onDelete,
    ): void {
        $this->dropForeignKeyIfExists($table, $column);

        Schema::table($table, function (Blueprint $blueprint) use ($column, $references, $on, $onDelete) {
            $foreign = $blueprint->foreign($column)->references($references)->on($on);

            match ($onDelete) {
                'cascade' => $foreign->cascadeOnDelete(),
                'null' => $foreign->nullOnDelete(),
                default => $foreign->restrictOnDelete(),
            };
        });
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $database = Schema::getConnection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$database, $table, $column],
        );

        if (! $row?->name) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($row) {
            $blueprint->dropForeign($row->name);
        });
    }
};
