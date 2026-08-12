<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\AdminNav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->subDays(13)->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $orders = Order::query()
            ->whereDate('placed_at', '>=', $from)
            ->whereDate('placed_at', '<=', $to);

        $paid = (clone $orders)->where('payment_status', 'paid');

        $salesByDay = (clone $paid)
            ->selectRaw('DATE(placed_at) as day, COUNT(*) as orders_count, COALESCE(SUM(total),0) as sales, COALESCE(SUM(tax_amount),0) as tax')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $salesByType = (clone $paid)
            ->selectRaw('type, COUNT(*) as orders_count, COALESCE(SUM(total),0) as sales')
            ->groupBy('type')
            ->orderByDesc('sales')
            ->get()
            ->map(fn ($row) => [
                'type' => match ($row->type) {
                    'dinein' => 'Dine-in',
                    'takeaway' => 'Takeaway',
                    'delivery' => 'Delivery',
                    'walkin' => 'Walk-in',
                    'qr' => 'QR Order',
                    default => ucfirst((string) $row->type),
                },
                'orders_count' => (int) $row->orders_count,
                'sales' => (float) $row->sales,
            ]);

        $topItems = OrderItem::query()
            ->select('item_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(line_total) as revenue'))
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('payment_status', 'paid')
                    ->whereDate('placed_at', '>=', $from)
                    ->whereDate('placed_at', '<=', $to);
            })
            ->groupBy('item_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $expenseByCategory = Expense::query()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category' => Expense::categories()[$row->category] ?? ucfirst((string) $row->category),
                'total' => (float) $row->total,
            ]);

        $totalSales = (float) (clone $paid)->sum('total');
        $totalTax = (float) (clone $paid)->sum('tax_amount');
        $totalOrders = (clone $paid)->count();
        $totalExpenses = (float) Expense::query()
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->sum('amount');

        return view('admin.finance.reports', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('reports'),
            'icons' => AdminNav::icons(),
            'filters' => ['from' => $from, 'to' => $to],
            'kpis' => [
                ['label' => 'Paid Sales', 'value' => $totalSales],
                ['label' => 'Paid Orders', 'value' => $totalOrders, 'plain' => true],
                ['label' => 'Tax Collected', 'value' => $totalTax],
                ['label' => 'Expenses', 'value' => $totalExpenses],
            ],
            'salesByDay' => $salesByDay,
            'salesByType' => $salesByType,
            'topItems' => $topItems,
            'expenseByCategory' => $expenseByCategory,
        ]);
    }
}
