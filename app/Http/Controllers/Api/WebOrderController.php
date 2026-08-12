<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use App\Models\TaxSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $settings = SiteSetting::current();
        if (! $settings->online_ordering_enabled) {
            return response()->json([
                'message' => 'Online ordering is currently disabled.',
            ], 403);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'type' => ['required', 'in:takeaway,delivery,dinein'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $order = null;

        DB::transaction(function () use ($data, &$order) {
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
                'order_number' => 'WEB-'.now()->format('ymd').'-'.str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT),
                'type' => $data['type'],
                'status' => 'pending',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'meta' => 'Website order',
                'subtotal' => $subtotal,
                'service_charge' => $service,
                'tax_amount' => $tax,
                'tip_amount' => 0,
                'discount_amount' => 0,
                'total' => $total,
                'payment_status' => 'unpaid',
                'is_held' => false,
                'notes' => $data['notes'] ?? null,
                'tags' => ['website'],
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                OrderItem::create(array_merge($line, ['order_id' => $order->id]));
            }
        });

        return response()->json([
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'status' => $order->status,
            'message' => 'Order placed successfully.',
        ], 201);
    }
}
