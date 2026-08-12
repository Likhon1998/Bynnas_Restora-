<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WastageRecord;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WastageController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type');

        $records = WastageRecord::query()
            ->with('inventoryItem')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest('recorded_on')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total_cost' => WastageRecord::sum('cost_impact'),
            'wastage' => WastageRecord::where('type', 'wastage')->count(),
            'variance' => WastageRecord::where('type', 'variance')->count(),
        ];

        return view('admin.wastage.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('wastage'),
            'icons' => AdminNav::icons(),
            'records' => $records,
            'stats' => $stats,
            'filters' => ['type' => $type],
        ]);
    }

    public function create(): View
    {
        return view('admin.wastage.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('wastage'),
            'icons' => AdminNav::icons(),
            'record' => new WastageRecord([
                'recorded_on' => now()->toDateString(),
                'type' => 'wastage',
            ]),
            'items' => InventoryItem::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $item = InventoryItem::findOrFail($data['inventory_item_id']);
            $qty = (float) $data['quantity'];
            $data['cost_impact'] = $qty * (float) $item->unit_cost;

            WastageRecord::create($data);

            if ($data['type'] === 'wastage') {
                $item->quantity_on_hand = max(0, (float) $item->quantity_on_hand - $qty);
                $item->save();
            }
        });

        return redirect()->route('admin.wastage.index')->with('success', 'Wastage / variance recorded.');
    }

    public function edit(WastageRecord $wastage): View
    {
        return view('admin.wastage.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('wastage'),
            'icons' => AdminNav::icons(),
            'record' => $wastage,
            'items' => InventoryItem::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, WastageRecord $wastage): RedirectResponse
    {
        $data = $this->validated($request);
        $item = InventoryItem::findOrFail($data['inventory_item_id']);
        $data['cost_impact'] = (float) $data['quantity'] * (float) $item->unit_cost;
        $wastage->update($data);

        return redirect()->route('admin.wastage.index')->with('success', 'Record updated.');
    }

    public function destroy(WastageRecord $wastage): RedirectResponse
    {
        $wastage->delete();

        return redirect()->route('admin.wastage.index')->with('success', 'Record deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:wastage,variance'],
            'recorded_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
