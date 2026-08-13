<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wire recipes to menu items and track whether an order has deducted stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('recipe_id')
                ->nullable()
                ->after('category')
                ->constrained('recipes')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('inventory_deducted')
                ->default(false)
                ->after('is_held');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->boolean('ledger_applied')
                ->default(false)
                ->after('status');
        });

        // Link seeded recipes to matching menu dishes when both exist.
        $salmonRecipeId = DB::table('recipes')->where('code', 'RCP-SAL')->value('id');
        $pastaRecipeId = DB::table('recipes')->where('code', 'RCP-TRF')->value('id');
        if ($salmonRecipeId) {
            DB::table('menu_items')->where('name', 'Grilled Salmon')->update(['recipe_id' => $salmonRecipeId]);
        }
        if ($pastaRecipeId) {
            DB::table('menu_items')->where('name', 'Truffle Pasta')->update(['recipe_id' => $pastaRecipeId]);
        }
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn('ledger_applied');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('inventory_deducted');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipe_id');
        });
    }
};
