<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 2 — Formal locations + per-location quantities + typed stock transfers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type', 40)->default('kitchen'); // kitchen|storage|bar|other
            $table->string('status', 20)->default('active'); // active|inactive
            $table->timestamps();
        });

        Schema::create('inventory_item_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->decimal('quantity', 14, 3)->default(0);
            $table->timestamps();

            $table->unique(['inventory_item_id', 'location_id']);
        });

        // Seed canonical locations + any free-text values already in use
        $now = now();
        $seedLocations = [
            ['name' => 'Main Kitchen', 'type' => 'kitchen', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Storage', 'type' => 'storage', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bar', 'type' => 'bar', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('locations')->insert($seedLocations);

        $existingNames = collect(
            DB::table('inventory_items')->whereNotNull('location')->distinct()->pluck('location')
        )->merge(
            DB::table('stock_transfers')->pluck('from_location')
        )->merge(
            DB::table('stock_transfers')->pluck('to_location')
        )->filter()->unique()->values();

        foreach ($existingNames as $name) {
            $exists = DB::table('locations')->where('name', $name)->exists();
            if (! $exists) {
                DB::table('locations')->insert([
                    'name' => $name,
                    'type' => 'other',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $locations = DB::table('locations')->pluck('id', 'name');
        $defaultLocationId = $locations['Main Kitchen'] ?? $locations->first();

        // Backfill per-location qty from cached quantity_on_hand
        $items = DB::table('inventory_items')->select('id', 'quantity_on_hand', 'location')->get();
        foreach ($items as $item) {
            $locationId = $locations[$item->location] ?? $defaultLocationId;
            DB::table('inventory_item_location')->insert([
                'inventory_item_id' => $item->id,
                'location_id' => $locationId,
                'quantity' => $item->quantity_on_hand ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Refactor stock_transfers to formal location FKs
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('from_location_id')->nullable()->after('inventory_item_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->nullable()->after('from_location_id')->constrained('locations')->restrictOnDelete();
        });

        $transfers = DB::table('stock_transfers')->get();
        foreach ($transfers as $transfer) {
            DB::table('stock_transfers')->where('id', $transfer->id)->update([
                'from_location_id' => $locations[$transfer->from_location] ?? $defaultLocationId,
                'to_location_id' => $locations[$transfer->to_location] ?? $defaultLocationId,
            ]);
        }

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn(['from_location', 'to_location']);
        });

        // Keep legacy free-text location for now (deprecated); Step 5+ services will prefer pivot.
        // Optionally nullable for gradual cutover:
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('default_location_id')->nullable()->after('location')->constrained('locations')->nullOnDelete();
        });

        DB::table('inventory_items')->update(['default_location_id' => $defaultLocationId]);
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_location_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
        });

        $locations = DB::table('locations')->pluck('name', 'id');
        foreach (DB::table('stock_transfers')->get() as $transfer) {
            DB::table('stock_transfers')->where('id', $transfer->id)->update([
                'from_location' => $locations[$transfer->from_location_id] ?? 'Main Kitchen',
                'to_location' => $locations[$transfer->to_location_id] ?? 'Storage',
            ]);
        }

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('from_location_id');
            $table->dropConstrainedForeignId('to_location_id');
        });

        Schema::dropIfExists('inventory_item_location');
        Schema::dropIfExists('locations');
    }
};
