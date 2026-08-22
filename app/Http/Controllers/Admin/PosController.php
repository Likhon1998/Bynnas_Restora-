<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\SiteSetting;
use App\Models\TaxSetting;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request): View
    {
        $menuItems = $this->availableMenuItems();

        $categories = $menuItems->pluck('category')->filter()->unique()->values();

        $heldOrders = Order::with(['table', 'items', 'customer'])
            ->where('is_held', true)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->limit(40)
            ->get();

        $heldCount = Order::query()
            ->where('is_held', true)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $openOrders = $this->openOrdersQuery()
            ->limit(40)
            ->get();

        $openTableOrders = $openOrders
            ->whereNotNull('table_id')
            ->unique('table_id')
            ->keyBy('table_id');

        $pendingCount = $openOrders->count();

        $tax = TaxSetting::current();
        $settings = SiteSetting::current();

        return view('admin.pos.index', [
            'user' => auth()->user(),
            'settings' => $settings,
            'menuItems' => $menuItems,
            'categories' => $categories,
            'tables' => RestaurantTable::orderBy('code')->get(),
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(),
            'waiters' => User::query()
                ->where('status', 'active')
                ->whereHas('role', fn ($q) => $q->whereIn('slug', ['waiter', 'cashier', 'manager']))
                ->orderBy('name')
                ->pluck('name')
                ->all() ?: ['Walk-in Service'],
            'heldOrders' => $heldOrders,
            'heldOrdersPayload' => $heldOrders->map(fn (Order $order) => $this->orderPayload($order)),
            'openOrders' => $openOrders,
            'openOrdersPayload' => $openOrders->map(fn (Order $order) => $this->orderPayload($order)),
            'openTableOrderNumbers' => $openTableOrders->mapWithKeys(
                fn (Order $order) => [$order->table_id => $order->order_number]
            ),
            'recentOrders' => Order::with('table')->latest('placed_at')->limit(10)->get(),
            'nextOrderNumber' => $this->nextOrderNumber(),
            'defaultType' => $request->get('type', 'dinein'),
            'notificationCount' => $heldOrders->count() + $openOrders->count(),
            'heldCount' => $heldCount,
            'taxSettings' => [
                'tax_name' => $tax->tax_name,
                'vat_rate' => (float) $tax->vat_rate,
                'service_charge_rate' => (float) $tax->service_charge_rate,
            ],
            'restaurant' => [
                'name' => $settings->restaurant_name ?: 'Bynnas Restora',
                'tagline' => $settings->tagline,
                'phone' => $settings->phone,
                'email' => $settings->email,
                'address' => trim(collect([
                    $settings->address_line1,
                    $settings->address_line2,
                    $settings->city,
                ])->filter()->implode(', ')),
                'currency' => $settings->currency_symbol ?: '৳',
            ],
        ]);
    }

    public function catalog(): JsonResponse
    {
        $items = $this->availableMenuItems()->map(fn (MenuItem $item) => $this->menuItemCard($item))->values();

        return response()->json([
            'items' => $items,
            'categories' => $items->pluck('category')->filter()->unique()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->merge([
            'table_id' => $request->filled('table_id') ? $request->input('table_id') : null,
            'resume_order_id' => $request->filled('resume_order_id') ? $request->input('resume_order_id') : null,
        ]);

        $data = $request->validate([
            'type' => ['required', 'in:dinein,takeaway,delivery,qr,walkin'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:40'],
            'meta' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['nullable', 'in:cash,bkash,card,split'],
            'cash_paid' => ['nullable', 'numeric', 'min:0'],
            'bkash_paid' => ['nullable', 'numeric', 'min:0'],
            'card_paid' => ['nullable', 'numeric', 'min:0'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'change_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'service_charge' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'apply_service' => ['nullable', 'boolean'],
            'apply_tax' => ['nullable', 'boolean'],
            'service_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'action' => ['required', 'in:pay,hold,save'],
            'resume_order_id' => ['nullable', 'exists:orders,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:120'],
        ]);

        $order = null;
        $invoicePayload = null;
        $tokensPayload = null;

        DB::transaction(function () use ($request, $data, &$order, &$invoicePayload, &$tokensPayload) {
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
                    'note' => $row['note'] ?? '',
                ];
            }

            $taxSettings = TaxSetting::current();
            $applyService = $request->boolean('apply_service');
            $applyTax = $request->boolean('apply_tax');
            $serviceRate = array_key_exists('service_rate', $data)
                ? (float) $data['service_rate']
                : (float) $taxSettings->service_charge_rate;
            $taxRate = array_key_exists('tax_rate', $data)
                ? (float) $data['tax_rate']
                : (float) $taxSettings->vat_rate;

            $service = $applyService ? round($subtotal * ($serviceRate / 100), 2) : 0.0;
            $tax = $applyTax ? round($subtotal * ($taxRate / 100), 2) : 0.0;
            $tip = (float) ($data['tip_amount'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total = max(0, round($subtotal + $service + $tax + $tip - $discount, 2));

            $isHold = $data['action'] === 'hold';
            $isSave = $data['action'] === 'save';
            $isPay = $data['action'] === 'pay';
            $isTableOrder = in_array($data['type'], ['dinein', 'qr', 'walkin'], true);
            $tableId = $isTableOrder && ! empty($data['table_id']) ? (int) $data['table_id'] : null;
            $payFirst = (bool) (SiteSetting::current()->pay_first ?? false);

            if ($payFirst && ($isHold || $isSave)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'action' => 'This restaurant is pay-first. Collect payment before the kitchen can receive the order.',
                ]);
            }

            if (! $payFirst && $isTableOrder && $isPay && empty($data['resume_order_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'action' => 'Dine-in cannot be paid directly. Send the order first, then collect payment.',
                ]);
            }

            $cashPaid = $isPay ? round((float) ($data['cash_paid'] ?? 0), 2) : 0.0;
            $bkashPaid = $isPay ? round((float) ($data['bkash_paid'] ?? 0), 2) : 0.0;
            $cardPaid = $isPay ? round((float) ($data['card_paid'] ?? 0), 2) : 0.0;
            $paidSum = round($cashPaid + $bkashPaid + $cardPaid, 2);
            $amountTendered = $isPay ? round((float) ($data['amount_tendered'] ?? $cashPaid), 2) : 0.0;
            $changeAmount = $isPay ? max(0, round($amountTendered - $cashPaid, 2)) : 0.0;

            if ($isPay && abs($paidSum - $total) > 0.05) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cash_paid' => 'Cash + bKash + Card must equal the order total (৳'.number_format($total, 2).').',
                ]);
            }

            $methodsUsed = collect([
                'cash' => $cashPaid,
                'bkash' => $bkashPaid,
                'card' => $cardPaid,
            ])->filter(fn ($v) => $v > 0);
            $paymentMethod = $isPay
                ? ($methodsUsed->count() > 1 ? 'split' : ($methodsUsed->keys()->first() ?? 'cash'))
                : null;

            $payLabel = $isPay
                ? ' · Pay: '.($paymentMethod === 'split'
                    ? collect([
                        $cashPaid > 0 ? 'Cash ৳'.number_format($cashPaid, 2) : null,
                        $bkashPaid > 0 ? 'bKash ৳'.number_format($bkashPaid, 2) : null,
                        $cardPaid > 0 ? 'Card ৳'.number_format($cardPaid, 2) : null,
                    ])->filter()->implode(' + ')
                    : strtoupper((string) $paymentMethod))
                : '';

            $existing = null;
            $previousKitchenMap = [];
            if (! empty($data['resume_order_id'])) {
                $existing = Order::query()
                    ->with('items')
                    ->whereKey($data['resume_order_id'])
                    ->where('payment_status', 'unpaid')
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->lockForUpdate()
                    ->first();

                if (! $existing) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'resume_order_id' => 'That order is no longer open. Find it again from Open Orders or by table.',
                    ]);
                }

                foreach ($existing->items as $oldLine) {
                    $key = $this->kitchenLineKey($oldLine->menu_item_id, $oldLine->item_name);
                    $previousKitchenMap[$key] = ($previousKitchenMap[$key] ?? 0) + (int) $oldLine->quantity;
                }
            } elseif ($isTableOrder && $tableId) {
                $tableOpen = $this->findOpenTableOrder($tableId, lock: true);
                if ($tableOpen) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'table_id' => 'Table already has open order '.$tableOpen->order_number.'. Load it to add more items.',
                    ]);
                }
            }

            $status = match (true) {
                $isPay => 'completed',
                $isHold => 'pending',
                default => 'preparing', // sent to kitchen
            };

            $orderAttributes = [
                'type' => $data['type'],
                'status' => $status,
                'table_id' => $tableId,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => filled($data['customer_name'] ?? null) ? trim($data['customer_name']) : null,
                'customer_phone' => filled($data['customer_phone'] ?? null) ? trim($data['customer_phone']) : null,
                'meta' => trim(($data['meta'] ?? '').$payLabel),
                'guest_count' => null,
                'subtotal' => $subtotal,
                'service_charge' => $service,
                'tax_amount' => $tax,
                'tip_amount' => $tip,
                'promo_code' => $data['promo_code'] ?? null,
                'discount_amount' => $discount,
                'total' => $total,
                'payment_status' => $isPay ? 'paid' : 'unpaid',
                'payment_method' => $paymentMethod,
                'cash_paid' => $cashPaid,
                'bkash_paid' => $bkashPaid,
                'card_paid' => $cardPaid,
                'amount_tendered' => $amountTendered,
                'change_amount' => $changeAmount,
                'is_held' => ($isHold || $isSave) && ! $isPay,
                'notes' => $data['notes'] ?? null,
                'tags' => $data['tags'] ?? [],
            ];

            if ($existing) {
                $existing->update($orderAttributes);
                $existing->items()->delete();
                $order = $existing;
            } else {
                $order = Order::create(array_merge($orderAttributes, [
                    'order_number' => $this->nextOrderNumber(),
                    'placed_at' => now(),
                ]));
            }

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $line['menu_item_id'],
                    'item_name' => $line['item_name'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }

            if ($isSave) {
                $kitchenItems = $this->kitchenDeltaItems($previousKitchenMap, $lines);
                $isAddition = $existing !== null;
                $tableCode = $tableId ? optional(RestaurantTable::find($tableId))->code : null;
                $tokenNo = $this->tokenNumberFromOrder($order->order_number);

                $tokensPayload = [
                    'order_number' => $order->order_number,
                    'token_number' => $tokenNo,
                    'is_addition' => $isAddition,
                    'type_label' => $order->typeLabel(),
                    'table' => $tableCode,
                    'seat_label' => $tableCode ? ('Table '.$tableCode) : 'No seat yet',
                    'customer_name' => $order->customer_name ?: 'Walk-in',
                    'cashier' => auth()->user()?->name,
                    'placed_at' => now()->format('M j, Y g:i A'),
                    'notes' => $order->notes,
                    'customer_items' => collect($lines)->map(fn ($line) => [
                        'name' => $line['item_name'],
                        'qty' => $line['quantity'],
                    ])->values(),
                    'kitchen_items' => $kitchenItems,
                    'item_count' => collect($lines)->sum('quantity'),
                    'subtotal' => (float) $subtotal,
                    'total' => (float) $total,
                ];
            }

            if ($isPay) {
                $this->inventory->consumeOrder($order->fresh(['items.menuItem.recipe.ingredients.inventoryItem']));

                $invoicePayload = [
                    'order_number' => $order->order_number,
                    'token_number' => $this->tokenNumberFromOrder($order->order_number),
                    'type' => $order->type,
                    'type_label' => $order->typeLabel(),
                    'customer_name' => $order->customer_name ?: 'Walk-in',
                    'customer_phone' => $order->customer_phone,
                    'table' => $tableId ? optional(RestaurantTable::find($tableId))->code : null,
                    'guest_count' => null,
                    'cashier' => auth()->user()?->name,
                    'placed_at' => optional($order->placed_at)->format('M j, Y g:i A'),
                    'items' => collect($lines)->map(fn ($line) => [
                        'name' => $line['item_name'],
                        'qty' => $line['quantity'],
                        'unit_price' => (float) $line['unit_price'],
                        'line_total' => (float) $line['line_total'],
                    ])->values(),
                    'subtotal' => (float) $subtotal,
                    'service_charge' => (float) $service,
                    'tax_amount' => (float) $tax,
                    'discount_amount' => (float) $discount,
                    'total' => (float) $total,
                    'cash_paid' => $cashPaid,
                    'bkash_paid' => $bkashPaid,
                    'card_paid' => $cardPaid,
                    'amount_tendered' => $amountTendered,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'notes' => $order->notes,
                ];
            }

            if ($tableId) {
                RestaurantTable::whereKey($tableId)->update([
                    'status' => $isPay ? 'available' : 'ordered',
                ]);
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
            'pay' => 'Payment captured · Token '.$this->tokenNumberFromOrder($order->order_number),
            'hold' => 'Order held · '.$order->order_number,
            default => 'Order sent · Token '.$this->tokenNumberFromOrder($order->order_number).' (unpaid / on hold until Pay)',
        };

        if ($request->expectsJson() || $request->ajax()) {
            $order->load(['table', 'items', 'customer']);

            return response()->json([
                'ok' => true,
                'message' => $msg,
                'order' => $this->orderPayload($order),
                'next_order_number' => $this->nextOrderNumber(),
                'tokens' => $tokensPayload,
                'invoice' => $invoicePayload,
            ]);
        }

        $redirect = redirect()->route('admin.pos.index')->with('success', $msg);
        if ($invoicePayload) {
            $redirect->with('invoice', $invoicePayload);
        }
        if ($tokensPayload) {
            $redirect->with('tokens', $tokensPayload);
        }

        return $redirect;
    }

    public function tableOrder(Request $request): JsonResponse
    {
        $tableId = (int) $request->validate([
            'table_id' => ['required', 'exists:restaurant_tables,id'],
        ])['table_id'];

        $order = $this->findOpenTableOrder($tableId);

        return response()->json([
            'order' => $order ? $this->orderPayload($order) : null,
        ]);
    }

    public function findOrder(Request $request): JsonResponse
    {
        $q = trim((string) $request->validate([
            'q' => ['required', 'string', 'max:80'],
        ])['q']);
        $q = ltrim($q, '#');

        $query = $this->unpaidTicketQuery();

        $order = $query->where(function ($inner) use ($q) {
            $inner->where('order_number', $q)
                ->orWhere('order_number', 'like', '%'.$q.'%');
            if (ctype_digit($q)) {
                $inner->orWhere('id', (int) $q);
            }
        })->first();

        return response()->json([
            'order' => $order ? $this->orderPayload($order) : null,
        ]);
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('ymd').'-';
        $latest = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $seq = 0;
        if (is_string($latest) && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function unpaidTicketQuery()
    {
        return Order::with(['table', 'items', 'customer'])
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('placed_at');
    }

    private function openOrdersQuery()
    {
        return $this->unpaidTicketQuery()->where('is_held', false);
    }

    private function findOpenTableOrder(int $tableId, bool $lock = false): ?Order
    {
        $query = $this->unpaidTicketQuery()
            ->whereNotNull('table_id')
            ->where('table_id', $tableId)
            ->whereIn('type', ['dinein', 'qr', 'walkin']);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function tokenNumberFromOrder(string $orderNumber): string
    {
        if (preg_match('/(\d+)$/', $orderNumber, $m)) {
            return $m[1];
        }

        return $orderNumber;
    }

    private function kitchenLineKey($menuItemId, string $itemName): string
    {
        $note = '';
        if (preg_match('/\(([^)]+)\)$/', $itemName, $m)) {
            $note = $m[1];
        }

        return (string) $menuItemId.'::'.$note;
    }

    private function kitchenDeltaItems(array $previousMap, array $lines): array
    {
        $delta = [];
        foreach ($lines as $line) {
            $key = $this->kitchenLineKey($line['menu_item_id'], $line['item_name']);
            $prev = (int) ($previousMap[$key] ?? 0);
            $qty = (int) $line['quantity'];
            $add = $qty - $prev;
            if ($add > 0) {
                $delta[] = [
                    'name' => $line['item_name'],
                    'qty' => $add,
                ];
            }
        }

        // First send (no previous): kitchen gets full order
        if (empty($previousMap)) {
            return collect($lines)->map(fn ($line) => [
                'name' => $line['item_name'],
                'qty' => $line['quantity'],
            ])->values()->all();
        }

        return $delta;
    }

    private function availableMenuItems()
    {
        return MenuItem::query()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function menuItemCard(MenuItem $item): array
    {
        $badge = $item->badge ?: ($item->is_bestseller ? 'Popular' : null);
        $badgeKey = strtolower((string) preg_replace('/\s+/', '', (string) $badge));
        $fallback = 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80';

        return [
            'id' => $item->id,
            'name' => $item->name,
            'price' => (float) $item->price,
            'category' => $item->category,
            'description' => $item->description,
            'image_url' => $item->image_url ?: $fallback,
            'badge' => $badge,
            'badge_key' => $badgeKey,
            'is_popular' => $badgeKey === 'popular' || $item->is_bestseller || $item->is_favorite,
            'is_bestseller' => $item->is_bestseller || $badgeKey === 'bestseller',
            'is_new' => $badgeKey === 'new',
            'is_spicy' => $badgeKey === 'spicy',
            'is_vegetarian' => (bool) $item->is_vegetarian,
            'is_favorite' => (bool) $item->is_favorite,
        ];
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'token_number' => $this->tokenNumberFromOrder($order->order_number),
            'type' => $order->type,
            'table_id' => $order->table_id,
            'table_code' => $order->table?->code,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name ?? $order->customer?->name ?? '',
            'customer_phone' => $order->customer_phone ?? $order->customer?->phone ?? '',
            'promo_code' => $order->promo_code,
            'discount_amount' => (float) $order->discount_amount,
            'service_charge' => (float) $order->service_charge,
            'tax_amount' => (float) $order->tax_amount,
            'apply_service' => (float) $order->service_charge > 0,
            'apply_tax' => (float) $order->tax_amount > 0,
            'notes' => $order->notes,
            'total' => (float) $order->total,
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
        ];
    }
}
