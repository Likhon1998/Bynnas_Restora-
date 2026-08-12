<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\WastageRecord;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $fresh = Supplier::query()->updateOrCreate(
            ['name' => 'Fresh Farm Co.'],
            ['contact_person' => 'Rahim Uddin', 'email' => 'orders@freshfarm.example', 'phone' => '+8801711000001', 'address' => 'Kawran Bazar, Dhaka', 'status' => 'active']
        );
        $ocean = Supplier::query()->updateOrCreate(
            ['name' => 'Ocean Catch Ltd.'],
            ['contact_person' => 'Mina Rahman', 'email' => 'sales@oceancatch.example', 'phone' => '+8801711000002', 'address' => 'Chittagong Port', 'status' => 'active']
        );
        $dairy = Supplier::query()->updateOrCreate(
            ['name' => 'Dairy Valley'],
            ['contact_person' => 'Karim Ali', 'email' => 'hello@dairyvalley.example', 'phone' => '+8801711000003', 'address' => 'Savar, Dhaka', 'status' => 'active']
        );
        $spice = Supplier::query()->updateOrCreate(
            ['name' => 'Spice Route'],
            ['contact_person' => 'Nusrat Jahan', 'email' => 'team@spiceroute.example', 'phone' => '+8801711000004', 'address' => 'Old Dhaka', 'status' => 'active']
        );

        $items = [
            ['sku' => 'INV-MOZ', 'name' => 'Mozzarella', 'category' => 'Dairy', 'unit' => 'kg', 'quantity_on_hand' => 5.2, 'reorder_level' => 15, 'unit_cost' => 850, 'supplier_id' => $dairy->id],
            ['sku' => 'INV-CHK', 'name' => 'Chicken Breast', 'category' => 'Meat', 'unit' => 'kg', 'quantity_on_hand' => 8.0, 'reorder_level' => 20, 'unit_cost' => 420, 'supplier_id' => $fresh->id],
            ['sku' => 'INV-OIL', 'name' => 'Olive Oil', 'category' => 'Pantry', 'unit' => 'L', 'quantity_on_hand' => 2.1, 'reorder_level' => 10, 'unit_cost' => 1200, 'supplier_id' => $spice->id],
            ['sku' => 'INV-RICE', 'name' => 'Basmati Rice', 'category' => 'Grains', 'unit' => 'kg', 'quantity_on_hand' => 12, 'reorder_level' => 25, 'unit_cost' => 140, 'supplier_id' => $fresh->id],
            ['sku' => 'INV-CRM', 'name' => 'Fresh Cream', 'category' => 'Dairy', 'unit' => 'L', 'quantity_on_hand' => 3.4, 'reorder_level' => 12, 'unit_cost' => 380, 'supplier_id' => $dairy->id],
            ['sku' => 'INV-SAL', 'name' => 'Salmon Fillet', 'category' => 'Seafood', 'unit' => 'kg', 'quantity_on_hand' => 18, 'reorder_level' => 10, 'unit_cost' => 1650, 'supplier_id' => $ocean->id],
            ['sku' => 'INV-PASTA', 'name' => 'Pasta', 'category' => 'Pantry', 'unit' => 'kg', 'quantity_on_hand' => 22, 'reorder_level' => 12, 'unit_cost' => 180, 'supplier_id' => $spice->id],
            ['sku' => 'INV-BEEF', 'name' => 'Beef Tenderloin', 'category' => 'Meat', 'unit' => 'kg', 'quantity_on_hand' => 9.5, 'reorder_level' => 8, 'unit_cost' => 1450, 'supplier_id' => $fresh->id],
        ];

        $itemMap = [];
        foreach ($items as $row) {
            $item = InventoryItem::query()->updateOrCreate(
                ['sku' => $row['sku']],
                array_merge($row, ['location' => 'Main Kitchen', 'status' => 'active'])
            );
            $itemMap[$row['sku']] = $item;
        }

        $salmon = Recipe::query()->updateOrCreate(
            ['code' => 'RCP-SAL'],
            ['name' => 'Grilled Salmon', 'yield_qty' => 1, 'yield_unit' => 'portion', 'selling_price' => 1450, 'status' => 'active']
        );
        $pasta = Recipe::query()->updateOrCreate(
            ['code' => 'RCP-TRF'],
            ['name' => 'Truffle Pasta', 'yield_qty' => 1, 'yield_unit' => 'portion', 'selling_price' => 980, 'status' => 'active']
        );

        RecipeIngredient::query()->whereIn('recipe_id', [$salmon->id, $pasta->id])->delete();
        RecipeIngredient::insert([
            ['recipe_id' => $salmon->id, 'inventory_item_id' => $itemMap['INV-SAL']->id, 'quantity' => 0.22, 'unit' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => $salmon->id, 'inventory_item_id' => $itemMap['INV-OIL']->id, 'quantity' => 0.02, 'unit' => 'L', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => $pasta->id, 'inventory_item_id' => $itemMap['INV-PASTA']->id, 'quantity' => 0.18, 'unit' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => $pasta->id, 'inventory_item_id' => $itemMap['INV-CRM']->id, 'quantity' => 0.08, 'unit' => 'L', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => $pasta->id, 'inventory_item_id' => $itemMap['INV-MOZ']->id, 'quantity' => 0.05, 'unit' => 'kg', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $po1 = PurchaseOrder::query()->updateOrCreate(
            ['po_number' => 'PO-2041'],
            ['supplier_id' => $fresh->id, 'order_date' => now()->subDay(), 'expected_date' => now()->addDays(2), 'status' => 'sent', 'total_amount' => 12600, 'notes' => 'Weekly produce']
        );
        $po2 = PurchaseOrder::query()->updateOrCreate(
            ['po_number' => 'PO-2040'],
            ['supplier_id' => $ocean->id, 'order_date' => now()->subDays(2), 'expected_date' => now()->subDay(), 'status' => 'received', 'total_amount' => 33000, 'notes' => null]
        );
        $po3 = PurchaseOrder::query()->updateOrCreate(
            ['po_number' => 'PO-2039'],
            ['supplier_id' => $dairy->id, 'order_date' => now()->subDays(3), 'status' => 'partial', 'total_amount' => 8500]
        );
        $po4 = PurchaseOrder::query()->updateOrCreate(
            ['po_number' => 'PO-2038'],
            ['supplier_id' => $spice->id, 'order_date' => now()->subDays(4), 'status' => 'draft', 'total_amount' => 4800]
        );

        PurchaseOrderItem::query()->whereIn('purchase_order_id', [$po1->id, $po2->id, $po3->id, $po4->id])->delete();
        PurchaseOrderItem::insert([
            ['purchase_order_id' => $po1->id, 'inventory_item_id' => $itemMap['INV-CHK']->id, 'quantity' => 30, 'unit_cost' => 420, 'received_qty' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['purchase_order_id' => $po2->id, 'inventory_item_id' => $itemMap['INV-SAL']->id, 'quantity' => 20, 'unit_cost' => 1650, 'received_qty' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['purchase_order_id' => $po3->id, 'inventory_item_id' => $itemMap['INV-MOZ']->id, 'quantity' => 10, 'unit_cost' => 850, 'received_qty' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['purchase_order_id' => $po4->id, 'inventory_item_id' => $itemMap['INV-OIL']->id, 'quantity' => 4, 'unit_cost' => 1200, 'received_qty' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        StockTransfer::query()->updateOrCreate(
            ['transfer_number' => 'ST-'.now()->format('ymd').'-001'],
            [
                'inventory_item_id' => $itemMap['INV-RICE']->id,
                'from_location' => 'Storage',
                'to_location' => 'Main Kitchen',
                'quantity' => 5,
                'transfer_date' => now()->subDay(),
                'status' => 'completed',
                'notes' => 'Dinner prep restock',
            ]
        );

        WastageRecord::query()->updateOrCreate(
            [
                'inventory_item_id' => $itemMap['INV-CRM']->id,
                'recorded_on' => now()->toDateString(),
                'reason' => 'Expired cream discarded',
            ],
            [
                'quantity' => 0.8,
                'type' => 'wastage',
                'cost_impact' => 0.8 * 380,
                'notes' => 'FIFO check',
            ]
        );
    }
}
