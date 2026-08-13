<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\SiteSetting;
use App\Models\TaxSetting;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QrOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function show(string $token): View
    {
        $table = RestaurantTable::query()->where('qr_token', $token)->firstOrFail();
        $settings = SiteSetting::current();
        $tax = TaxSetting::current();

        $menuItems = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = $menuItems->pluck('category')->filter()->unique()->values();

        $menuPayload = $menuItems->map(fn (MenuItem $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float) $item->price,
            'category' => $item->category,
            'image' => $item->image_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=400&q=80',
        ])->values();

        return view('qr.order', [
            'table' => $table,
            'settings' => $settings,
            'menuItems' => $menuPayload,
            'categories' => $categories,
            'tax' => [
                'vat_rate' => (float) $tax->vat_rate,
                'service_charge_rate' => (float) $tax->service_charge_rate,
                'tax_name' => $tax->tax_name,
            ],
            'orderingEnabled' => (bool) $settings->online_ordering_enabled,
        ]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $settings = SiteSetting::current();
        if (! $settings->online_ordering_enabled) {
            return response()->json([
                'message' => 'Online ordering is currently disabled.',
            ], 403);
        }

        $table = RestaurantTable::query()->where('qr_token', $token)->firstOrFail();

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $order = null;

        try {
            DB::transaction(function () use ($data, $table, &$order) {
                $subtotal = 0;
                $lines = [];

                foreach ($data['items'] as $row) {
                    $menu = MenuItem::query()
                        ->whereKey($row['menu_item_id'])
                        ->where('is_available', true)
                        ->firstOrFail();

                    $qty = (int) $row['quantity'];
                    $lineTotal = $qty * (float) $menu->price;
                    $subtotal += $lineTotal;

                    $lines[] = [
                        'menu_item_id' => $menu->id,
                        'item_name' => $menu->name,
                        'quantity' => $qty,
                        'unit_price' => $menu->price,
                        'line_total' => $lineTotal,
                    ];
                }

                $taxSettings = TaxSetting::current();
                $service = round($subtotal * $taxSettings->serviceFraction(), 2);
                $tax = round($subtotal * $taxSettings->vatFraction(), 2);
                $total = max(0, $subtotal + $service + $tax);

                $order = Order::create([
                    'order_number' => 'QR-'.now()->format('ymd').'-'.str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT),
                    'type' => 'qr',
                    'status' => 'pending',
                    'table_id' => $table->id,
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'meta' => 'QR Table '.$table->code,
                    'subtotal' => $subtotal,
                    'service_charge' => $service,
                    'tax_amount' => $tax,
                    'tip_amount' => 0,
                    'discount_amount' => 0,
                    'total' => $total,
                    'payment_status' => 'unpaid',
                    'is_held' => false,
                    'notes' => $data['notes'] ?? null,
                    'tags' => ['qr', 'table-'.$table->code],
                    'placed_at' => now(),
                ]);

                foreach ($lines as $line) {
                    OrderItem::create(array_merge($line, ['order_id' => $order->id]));
                }

                $table->update(['status' => 'ordered']);

                $this->inventory->consumeOrder(
                    $order->fresh(['items.menuItem.recipe.ingredients.inventoryItem'])
                );
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => $e->getMessage() ?: 'Could not place order.',
            ], 422);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'status' => $order->status,
            'table' => $table->code,
            'message' => 'Order sent to the kitchen.',
        ], 201);
    }
}
