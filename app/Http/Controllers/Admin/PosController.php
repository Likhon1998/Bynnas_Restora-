<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $menuItems = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = $menuItems->pluck('category')->filter()->unique()->values();

        $heldOrders = Order::with(['table', 'items', 'customer'])
            ->where('is_held', true)
            ->latest()
            ->limit(20)
            ->get();

        $pendingCount = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->where('is_held', false)
            ->count();

        return view('admin.pos.index', [
            'user' => auth()->user(),
            'menuItems' => $menuItems,
            'categories' => $categories,
            'tables' => RestaurantTable::orderBy('code')->get(),
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(),
            'waiters' => ['Rahim Uddin', 'Karim Ali', 'Nusrat Jahan', 'Mina Rahman'],
            'heldOrders' => $heldOrders,
            'heldOrdersPayload' => $heldOrders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'type' => $order->type,
                'table_id' => $order->table_id,
                'table_code' => $order->table?->code,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name ?? $order->customer?->name ?? 'Walk-in Customer',
                'guest_count' => $order->guest_count,
                'promo_code' => $order->promo_code,
                'discount_amount' => (float) $order->discount_amount,
                'items' => $order->items->map(function ($line) {
                    $note = '';
                    if (preg_match('/\(([^)]+)\)$/', $line->item_name, $m)) {
                        $note = $m[1];
                    }
                    $name = preg_replace('/\s*\([^)]+\)$/', '', $line->item_name);

                    return [
                        'menu_item_id' => $line->menu_item_id,
                        'name' => $name,
                        'price' => (float) $line->unit_price,
                        'qty' => (int) $line->quantity,
                        'note' => $note,
                    ];
                })->values(),
            ]),
            'recentOrders' => Order::with('table')->latest('placed_at')->limit(10)->get(),
            'nextOrderNumber' => 'ORD-'.now()->format('ymd').'-'.str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT),
            'defaultType' => $request->get('type', 'dinein'),
            'notificationCount' => $heldOrders->count() + min($pendingCount, 5),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:dinein,takeaway,delivery,qr,walkin'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:40'],
            'meta' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['nullable', 'in:cash,card,online'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'service_charge' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'action' => ['required', 'in:pay,hold,save'],
            'resume_order_id' => ['nullable', 'exists:orders,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:120'],
        ]);

        $order = null;

        DB::transaction(function () use ($data, &$order) {
            if (! empty($data['resume_order_id'])) {
                Order::whereKey($data['resume_order_id'])
                    ->where('is_held', true)
                    ->update(['is_held' => false, 'status' => 'cancelled']);
            }

            $subtotal = 0;
            $lines = [];
            foreach ($data['items'] as $row) {
                $menu = MenuItem::findOrFail($row['menu_item_id']);
                $qty = (int) $row['quantity'];
                $line = $qty * (float) $menu->price;
                $subtotal += $line;
                $lines[] = [
                    'menu_item_id' => $menu->id,
                    'item_name' => $menu->name.(! empty($row['note']) ? ' ('.$row['note'].')' : ''),
                    'quantity' => $qty,
                    'unit_price' => $menu->price,
                    'line_total' => $line,
                ];
            }

            $service = (float) ($data['service_charge'] ?? round($subtotal * 0.05, 2));
            $tax = (float) ($data['tax_amount'] ?? round($subtotal * 0.07, 2));
            $tip = (float) ($data['tip_amount'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total = max(0, $subtotal + $service + $tax + $tip - $discount);

            $isHold = in_array($data['action'], ['hold', 'save'], true);
            $isPay = $data['action'] === 'pay';

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('ymd').'-'.str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT),
                'type' => $data['type'],
                'status' => $isPay ? 'preparing' : 'pending',
                'table_id' => $data['table_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'meta' => trim(($data['meta'] ?? '').(isset($data['payment_method']) ? ' · Pay: '.$data['payment_method'] : '')),
                'guest_count' => $data['guest_count'] ?? null,
                'subtotal' => $subtotal,
                'service_charge' => $service,
                'tax_amount' => $tax,
                'tip_amount' => $tip,
                'promo_code' => $data['promo_code'] ?? null,
                'discount_amount' => $discount,
                'total' => $total,
                'payment_status' => $isPay ? 'paid' : 'unpaid',
                'is_held' => $isHold,
                'notes' => $data['notes'] ?? null,
                'tags' => $data['tags'] ?? [],
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                OrderItem::create(array_merge($line, ['order_id' => $order->id]));
            }

            if (! empty($data['table_id']) && $isPay) {
                RestaurantTable::whereKey($data['table_id'])->update(['status' => 'ordered']);
            }

            if ($isPay && ! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $points = (int) floor($total / 100);
                    $customer->loyalty_points += $points;
                    $customer->lifetime_spend = (float) $customer->lifetime_spend + $total;
                    $customer->save();
                }
            }
        });

        $msg = match ($data['action']) {
            'pay' => 'Payment captured · '.$order->order_number,
            'hold' => 'Order held · '.$order->order_number,
            default => 'Order saved · '.$order->order_number,
        };

        return redirect()->route('admin.pos.index')->with('success', $msg);
    }
}
