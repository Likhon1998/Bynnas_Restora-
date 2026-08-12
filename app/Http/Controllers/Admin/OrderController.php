<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type');
        $status = $request->get('status');
        $q = trim((string) $request->get('q', ''));

        $orders = Order::query()
            ->with(['table', 'customer', 'items'])
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('order_number', 'ilike', "%{$q}%")
                    ->orWhere('customer_name', 'ilike', "%{$q}%")
                    ->orWhere('meta', 'ilike', "%{$q}%");
            }))
            ->latest('placed_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'open' => Order::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'today_sales' => Order::whereDate('placed_at', today())->where('payment_status', 'paid')->sum('total'),
        ];

        return view('admin.orders.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('orders'),
            'icons' => AdminNav::icons(),
            'orders' => $orders,
            'stats' => $stats,
            'filters' => compact('type', 'status', 'q'),
        ]);
    }

    public function edit(Order $order): View
    {
        $order->load(['items', 'table', 'customer']);

        return view('admin.orders.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('orders'),
            'icons' => AdminNav::icons(),
            'order' => $order,
            'tables' => RestaurantTable::orderBy('code')->get(),
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,preparing,ready,on_the_way,completed,cancelled'],
            'payment_status' => ['required', 'in:unpaid,paid,refunded'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'meta' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $order->update($data);

        if (! empty($data['table_id'])) {
            $tableStatus = match ($data['status']) {
                'ready' => 'ready',
                'preparing', 'pending' => 'preparing',
                'completed', 'cancelled' => 'available',
                default => 'ordered',
            };
            RestaurantTable::whereKey($data['table_id'])->update(['status' => $tableStatus]);
        }

        if ($data['status'] === 'completed' && $data['payment_status'] === 'paid' && $order->customer_id) {
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $points = (int) floor((float) $order->total / 100);
                if ($points > 0) {
                    $customer->loyalty_points += $points;
                    $customer->lifetime_spend = (float) $customer->lifetime_spend + (float) $order->total;
                    $customer->save();
                }
            }
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}
