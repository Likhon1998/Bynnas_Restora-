<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $suppliers = Supplier::query()
            ->withCount('purchaseOrders')
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.suppliers.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('suppliers'),
            'icons' => AdminNav::icons(),
            'suppliers' => $suppliers,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): View
    {
        return view('admin.suppliers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('suppliers'),
            'icons' => AdminNav::icons(),
            'supplier' => new Supplier(['status' => 'active']),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('suppliers'),
            'icons' => AdminNav::icons(),
            'supplier' => $supplier,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
