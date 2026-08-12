<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 3 — Unit conversions (purchase unit ↔ recipe/base unit).
 *
 * Recipes and ledger quantities should use base_unit.
 * Purchase orders continue to use purchase_unit.
 * conversion_rate = how many base units equal 1 purchase unit (e.g. L→ml = 1000).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('base_unit', 32)->nullable()->after('unit');
            $table->string('purchase_unit', 32)->nullable()->after('base_unit');
            $table->decimal('conversion_rate', 14, 6)->default(1)->after('purchase_unit');
        });

        $items = DB::table('inventory_items')->select('id', 'unit')->get();

        foreach ($items as $item) {
            $unit = strtolower(trim((string) $item->unit));
            [$base, $purchase, $rate] = match ($unit) {
                'kg', 'kilogram', 'kilograms' => ['g', 'kg', 1000],
                'g', 'gram', 'grams' => ['g', 'g', 1],
                'l', 'liter', 'litre', 'liters', 'litres' => ['ml', 'L', 1000],
                'ml', 'milliliter', 'millilitre' => ['ml', 'ml', 1],
                'pcs', 'pc', 'piece', 'pieces' => ['pcs', 'pcs', 1],
                default => [$item->unit ?: 'unit', $item->unit ?: 'unit', 1],
            };

            DB::table('inventory_items')->where('id', $item->id)->update([
                'base_unit' => $base,
                'purchase_unit' => $purchase,
                'conversion_rate' => $rate,
            ]);
        }

        // Keep legacy `unit` column as alias of purchase_unit for backward-compatible UI.
        // Controllers/views can migrate gradually to purchase_unit / base_unit.
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['base_unit', 'purchase_unit', 'conversion_rate']);
        });
    }
};
