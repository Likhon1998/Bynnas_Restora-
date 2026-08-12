<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\WastageRecord;
use App\Support\AdminNav;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $salesQuery = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('placed_at', '>=', $from)
            ->whereDate('placed_at', '<=', $to);

        $sales = (float) (clone $salesQuery)->sum('total');
        $taxCollected = (float) (clone $salesQuery)->sum('tax_amount');
        $serviceCollected = (float) (clone $salesQuery)->sum('service_charge');
        $discounts = (float) (clone $salesQuery)->sum('discount_amount');
        $orderCount = (clone $salesQuery)->count();

        $expenses = (float) Expense::query()
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->sum('amount');

        $purchaseSpend = (float) PurchaseOrder::query()
            ->whereIn('status', ['sent', 'partial', 'received'])
            ->whereDate('order_date', '>=', $from)
            ->whereDate('order_date', '<=', $to)
            ->sum('total_amount');

        $wastageCost = (float) WastageRecord::query()
            ->whereDate('recorded_on', '>=', $from)
            ->whereDate('recorded_on', '<=', $to)
            ->sum('cost_impact');

        $cogs = $purchaseSpend + $wastageCost;
        $gross = $sales - $cogs;
        $net = $gross - $expenses;

        $ledger = collect();

        Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('placed_at', '>=', $from)
            ->whereDate('placed_at', '<=', $to)
            ->latest('placed_at')
            ->limit(40)
            ->get()
            ->each(function (Order $order) use ($ledger) {
                $ledger->push([
                    'date' => optional($order->placed_at)->format('M d, Y H:i'),
                    'type' => 'Income',
                    'tone' => 'green',
                    'reference' => $order->order_number,
                    'description' => $order->typeLabel().' · '.($order->customer_name ?: 'Walk-in'),
                    'in' => (float) $order->total,
                    'out' => 0.0,
                ]);
            });

        Expense::query()
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->latest('expense_date')
            ->limit(40)
            ->get()
            ->each(function (Expense $expense) use ($ledger) {
                $ledger->push([
                    'date' => optional($expense->expense_date)->format('M d, Y'),
                    'type' => 'Expense',
                    'tone' => 'red',
                    'reference' => $expense->reference ?: ('EXP-'.$expense->id),
                    'description' => $expense->title.' · '.$expense->categoryLabel(),
                    'in' => 0.0,
                    'out' => (float) $expense->amount,
                ]);
            });

        $ledger = $ledger->sortByDesc('date')->values();

        return view('admin.finance.accounting', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('accounting'),
            'icons' => AdminNav::icons(),
            'filters' => ['from' => $from, 'to' => $to],
            'summary' => [
                ['label' => 'Paid Sales', 'value' => $sales, 'meta' => $orderCount.' orders'],
                ['label' => 'COGS (PO + Wastage)', 'value' => $cogs, 'meta' => 'Purchases ৳ '.number_format($purchaseSpend, 2)],
                ['label' => 'Gross Profit', 'value' => $gross, 'meta' => $sales > 0 ? number_format(($gross / $sales) * 100, 1).'%' : '—'],
                ['label' => 'Operating Expenses', 'value' => $expenses, 'meta' => 'From Expenses module'],
                ['label' => 'Tax Collected', 'value' => $taxCollected, 'meta' => 'Service ৳ '.number_format($serviceCollected, 2)],
                ['label' => 'Net Profit', 'value' => $net, 'meta' => $sales > 0 ? number_format(($net / $sales) * 100, 1).'%' : '—', 'highlight' => true],
            ],
            'discounts' => $discounts,
            'ledger' => $ledger,
        ]);
    }
}
