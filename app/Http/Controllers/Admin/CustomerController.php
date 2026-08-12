<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $customers = Customer::query()
            ->withCount(['orders', 'reservations'])
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('customers'),
            'icons' => AdminNav::icons(),
            'customers' => $customers,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('customers'),
            'icons' => AdminNav::icons(),
            'customer' => new Customer([
                'membership_tier' => 'standard',
                'status' => 'active',
                'joined_on' => now()->toDateString(),
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('customers'),
            'icons' => AdminNav::icons(),
            'customer' => $customer,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request, $customer->id));

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', 'unique:customers,email,'.($id ?? 'NULL')],
            'phone' => ['nullable', 'string', 'max:40'],
            'membership_tier' => ['required', 'in:standard,silver,gold,platinum'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'lifetime_spend' => ['nullable', 'numeric', 'min:0'],
            'joined_on' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
