<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TaxSetting;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        $settings = TaxSetting::current();
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $paid = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('placed_at', '>=', $from)
            ->whereDate('placed_at', '<=', $to);

        $taxRows = (clone $paid)
            ->selectRaw('DATE(placed_at) as day, COUNT(*) as orders_count, COALESCE(SUM(subtotal),0) as taxable, COALESCE(SUM(tax_amount),0) as tax, COALESCE(SUM(service_charge),0) as service')
            ->groupBy('day')
            ->orderByDesc('day')
            ->paginate(20)
            ->withQueryString();

        return view('admin.finance.tax', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('tax'),
            'icons' => AdminNav::icons(),
            'settings' => $settings,
            'filters' => ['from' => $from, 'to' => $to],
            'totals' => [
                'taxable' => (float) (clone $paid)->sum('subtotal'),
                'tax' => (float) (clone $paid)->sum('tax_amount'),
                'service' => (float) (clone $paid)->sum('service_charge'),
                'orders' => (clone $paid)->count(),
            ],
            'taxRows' => $taxRows,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tax_name' => ['required', 'string', 'max:80'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_charge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $settings = TaxSetting::current();
        $settings->update([
            'tax_name' => $data['tax_name'],
            'vat_rate' => $data['vat_rate'],
            'service_charge_rate' => $data['service_charge_rate'],
            'is_active' => $request->boolean('is_active', true),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.tax.index')->with('success', 'Tax settings updated. POS will use the new rates.');
    }
}
