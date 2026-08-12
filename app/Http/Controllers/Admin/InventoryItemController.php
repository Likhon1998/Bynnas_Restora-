<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        $items = InventoryItem::query()
            ->with('supplier')
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'ilike', "%{$q}%")
                    ->orWhere('sku', 'ilike', "%{$q}%")
                    ->orWhere('category', 'ilike', "%{$q}%");
            }))
            ->when($status === 'low', fn ($query) => $query->whereColumn('quantity_on_hand', '<=', 'reorder_level'))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => InventoryItem::count(),
            'low' => InventoryItem::whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
            'value' => InventoryItem::selectRaw('COALESCE(SUM(quantity_on_hand * unit_cost), 0) as total')->value('total'),
        ];

        return view('admin.inventory.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('inventory'),
            'icons' => AdminNav::icons(),
            'items' => $items,
            'stats' => $stats,
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function create(): View
    {
        return view('admin.inventory.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('inventory'),
            'icons' => AdminNav::icons(),
            'item' => new InventoryItem(['unit' => 'kg', 'status' => 'active', 'location' => 'Main Kitchen']),
            'suppliers' => Supplier::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        InventoryItem::create($data);

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item created.');
    }

    public function edit(InventoryItem $inventory): View
    {
        return view('admin.inventory.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('inventory'),
            'icons' => AdminNav::icons(),
            'item' => $inventory,
            'suppliers' => Supplier::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $inventory->update($this->validated($request, $inventory->id));

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item updated.');
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:inventory_items,sku,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:80'],
            'unit' => ['required', 'string', 'max:32'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
