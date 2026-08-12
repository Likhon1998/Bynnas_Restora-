<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Support\AdminNav;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('dashboard'),
            'icons' => AdminNav::icons(),
            'kpis' => $this->kpis(),
            'tableLegend' => $this->tableLegend(),
            'floorTables' => $this->floorTables(),
            'orderTabs' => $this->orderTabs(),
            'liveOrders' => $this->liveOrders(),
            'revenueWeek' => $this->revenueWeek(),
            'inventoryAlerts' => $this->inventoryAlerts(),
            'purchaseOrders' => $this->purchaseOrders(),
            'financialSnapshot' => $this->financialSnapshot(),
            'topSelling' => $this->topSelling(),
            'upcomingReservations' => $this->upcomingReservations(),
            'staffPerformance' => $this->staffPerformance(),
            'crmStats' => $this->crmStats(),
            'recentActivities' => $this->recentActivities(),
        ]);
    }

    private function kpis(): array
    {
        $todaySales = (float) Order::whereDate('placed_at', today())->sum('total');
        $todayOrders = Order::whereDate('placed_at', today())->count();
        $todayReservations = Reservation::whereDate('reserved_at', today())->count();
        $newCustomers = Customer::whereDate('created_at', '>=', now()->subDays(7))->count();
        $aov = $todayOrders > 0 ? $todaySales / $todayOrders : 0;

        return [
            ['title' => 'Total Revenue', 'value' => '৳ '.number_format($todaySales ?: 185420.50, 2), 'change' => '+14.6%', 'tone' => 'gold'],
            ['title' => 'Total Orders', 'value' => (string) ($todayOrders ?: Order::count()), 'change' => '+12.3%', 'tone' => 'purple'],
            ['title' => 'Reservations', 'value' => (string) ($todayReservations ?: Reservation::count()), 'change' => '+8.9%', 'tone' => 'blue'],
            ['title' => 'New Customers', 'value' => (string) ($newCustomers ?: Customer::count()), 'change' => '+15.8%', 'tone' => 'green'],
            ['title' => 'Average Order Value', 'value' => '৳ '.number_format($aov ?: 423.45, 2), 'change' => '+9.4%', 'tone' => 'orange'],
        ];
    }

    private function tableLegend(): array
    {
        $colors = [
            'seated' => '#3b82f6',
            'ordered' => '#a855f7',
            'preparing' => '#f59e0b',
            'ready' => '#22c55e',
            'waiting' => '#ef4444',
            'available' => '#64748b',
        ];

        return collect($colors)->map(fn ($color, $status) => [
            'label' => ucfirst($status),
            'count' => RestaurantTable::where('status', $status)->count(),
            'color' => $color,
        ])->values()->all();
    }

    private function floorTables(): array
    {
        return RestaurantTable::orderBy('code')->get()->map(fn (RestaurantTable $t) => [
            'id' => $t->code,
            'status' => $t->status,
        ])->all();
    }

    private function orderTabs(): array
    {
        return [
            ['key' => 'all', 'label' => 'All', 'count' => Order::whereNotIn('status', ['completed', 'cancelled'])->count()],
            ['key' => 'dinein', 'label' => 'Dine-in', 'count' => Order::where('type', 'dinein')->whereNotIn('status', ['completed', 'cancelled'])->count()],
            ['key' => 'takeaway', 'label' => 'Takeaway', 'count' => Order::where('type', 'takeaway')->whereNotIn('status', ['completed', 'cancelled'])->count()],
            ['key' => 'delivery', 'label' => 'Delivery', 'count' => Order::where('type', 'delivery')->whereNotIn('status', ['completed', 'cancelled'])->count()],
        ];
    }

    private function liveOrders(): array
    {
        return Order::query()
            ->with('table')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('placed_at')
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'id' => '#'.$order->order_number,
                'meta' => $order->typeLabel().' · '.($order->meta ?: ($order->table?->code ? 'Table '.$order->table->code : '—')),
                'ago' => optional($order->placed_at)->diffForHumans() ?? 'just now',
                'status' => $order->statusLabel(),
                'tone' => $order->statusTone(),
            ])
            ->all();
    }

    private function revenueWeek(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days[] = [
                'day' => $day->format('D'),
                'value' => (float) Order::whereDate('placed_at', $day)->sum('total') ?: (14000 + (6 - $i) * 1200),
            ];
        }

        return $days;
    }

    private function inventoryAlerts(): array
    {
        return InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get()
            ->map(fn (InventoryItem $item) => [
                'name' => $item->name,
                'left' => rtrim(rtrim(number_format((float) $item->quantity_on_hand, 3, '.', ''), '0'), '.').' '.$item->unit,
                'min' => rtrim(rtrim(number_format((float) $item->reorder_level, 3, '.', ''), '0'), '.').' '.$item->unit,
                'pct' => max(5, min(100, $item->stockPercent())),
            ])
            ->all();
    }

    private function purchaseOrders(): array
    {
        return PurchaseOrder::query()
            ->with('supplier')
            ->latest('order_date')
            ->limit(4)
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'id' => $po->po_number,
                'supplier' => $po->supplier?->name ?? '—',
                'date' => optional($po->order_date)->format('M d, Y'),
                'status' => $po->statusLabel(),
                'tone' => $po->statusTone(),
            ])
            ->all();
    }

    private function financialSnapshot(): array
    {
        $sales = (float) Order::where('payment_status', 'paid')->sum('total');
        $expenses = (float) Expense::query()->sum('amount');
        $purchaseSpend = (float) PurchaseOrder::query()
            ->whereIn('status', ['sent', 'partial', 'received'])
            ->sum('total_amount');
        $cogs = $purchaseSpend > 0 ? $purchaseSpend : ($sales > 0 ? $sales * 0.39 : 0);
        if ($sales <= 0 && $expenses <= 0) {
            $sales = 185420.50;
            $cogs = $sales * 0.39;
            $expenses = $sales * 0.15;
        }
        $gross = $sales - $cogs;
        $net = $gross - $expenses;

        return [
            ['label' => 'Total Sales', 'value' => '৳ '.number_format($sales, 2)],
            ['label' => 'Cost of Goods Sold', 'value' => '৳ '.number_format($cogs, 2)],
            ['label' => 'Gross Profit', 'value' => '৳ '.number_format($gross, 2), 'meta' => $sales > 0 ? number_format(($gross / $sales) * 100, 1).'%' : '—'],
            ['label' => 'Total Expenses', 'value' => '৳ '.number_format($expenses, 2)],
            ['label' => 'Net Profit', 'value' => '৳ '.number_format($net, 2), 'meta' => $sales > 0 ? number_format(($net / $sales) * 100, 1).'%' : '—', 'highlight' => true],
        ];
    }

    private function topSelling(): array
    {
        return \App\Models\MenuItem::query()
            ->withSum('orderItems as sold_qty', 'quantity')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get()
            ->values()
            ->map(function ($item, $i) {
                $sold = (int) ($item->sold_qty ?? 0);

                return [
                    'rank' => $i + 1,
                    'name' => $item->name,
                    'sold' => $sold,
                    'revenue' => '৳ '.number_format($sold * (float) $item->price, 0),
                    'image' => $item->image_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=100&q=80',
                ];
            })
            ->all();
    }

    private function upcomingReservations(): array
    {
        return Reservation::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('reserved_at', '>=', now()->startOfDay())
            ->orderBy('reserved_at')
            ->limit(5)
            ->get()
            ->map(fn (Reservation $r) => [
                'time' => $r->reserved_at?->format('h:i A'),
                'name' => $r->guest_name,
                'guests' => $r->guests,
                'status' => ucfirst($r->status),
                'tone' => $r->statusTone(),
            ])
            ->all();
    }

    private function staffPerformance(): array
    {
        $total = User::count();
        $active = User::where('status', 'active')->count();
        $presentPct = $total > 0 ? (int) round(($active / $total) * 100) : 0;
        $paidSales = (float) Order::where('payment_status', 'paid')->whereDate('placed_at', today())->sum('total');
        $paidOrders = Order::where('payment_status', 'paid')->whereDate('placed_at', today())->count();
        $salesPerStaff = $active > 0 ? $paidSales / $active : 0;
        $ordersPerStaff = $active > 0 ? $paidOrders / $active : 0;
        $salaryExpenses = (float) Expense::query()
            ->where('category', 'salaries')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $monthSales = (float) Order::where('payment_status', 'paid')
            ->whereMonth('placed_at', now()->month)
            ->whereYear('placed_at', now()->year)
            ->sum('total');
        $laborPct = $monthSales > 0 ? ($salaryExpenses / $monthSales) * 100 : 0;

        return [
            ['label' => 'Present', 'value' => $active.'/'.max($total, 1), 'pct' => max(5, min(100, $presentPct ?: 5)), 'change' => null],
            ['label' => 'Sales / Staff', 'value' => '৳ '.number_format($salesPerStaff, 0), 'pct' => max(5, min(100, (int) ($salesPerStaff > 0 ? 74 : 5))), 'change' => null],
            ['label' => 'Orders / Staff', 'value' => number_format($ordersPerStaff, 1), 'pct' => max(5, min(100, (int) ($ordersPerStaff > 0 ? 68 : 5))), 'change' => null],
            ['label' => 'Labor Cost %', 'value' => number_format($laborPct, 1).'%', 'pct' => max(5, min(100, (int) round($laborPct ?: 5))), 'change' => null],
        ];
    }

    private function crmStats(): array
    {
        $total = Customer::count();
        $members = Customer::whereIn('membership_tier', ['silver', 'gold', 'platinum'])->count();
        $redeemed = abs((int) LoyaltyTransaction::where('type', 'redeem')->sum('points'));

        return [
            ['label' => 'Total Customers', 'value' => number_format($total), 'change' => '+6.2%'],
            ['label' => 'Members', 'value' => number_format($members), 'change' => '+4.1%'],
            ['label' => 'Points Redeemed', 'value' => number_format($redeemed), 'change' => '+9.8%'],
        ];
    }

    private function recentActivities(): array
    {
        $activities = [];
        foreach (Order::latest('placed_at')->limit(3)->get() as $order) {
            $activities[] = [
                'text' => 'Order #'.$order->order_number.' · '.$order->statusLabel(),
                'time' => optional($order->placed_at)->diffForHumans() ?? 'just now',
                'tone' => $order->statusTone() === 'amber' ? 'orange' : $order->statusTone(),
            ];
        }
        foreach (Reservation::latest()->limit(2)->get() as $r) {
            $activities[] = [
                'text' => 'Reservation '.$r->status.' for '.$r->guest_name,
                'time' => $r->updated_at?->diffForHumans() ?? 'just now',
                'tone' => 'blue',
            ];
        }
        $low = InventoryItem::whereColumn('quantity_on_hand', '<=', 'reorder_level')->orderBy('quantity_on_hand')->first();
        if ($low) {
            $activities[] = [
                'text' => 'Stock update: '.$low->name.' low',
                'time' => 'recently',
                'tone' => 'red',
            ];
        }

        return array_slice($activities, 0, 6) ?: [
            ['text' => 'System ready', 'time' => 'just now', 'tone' => 'green'],
        ];
    }
}
