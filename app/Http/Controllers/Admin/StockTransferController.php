<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(): View
    {
        $transfers = StockTransfer::query()
            ->with('inventoryItem')
            ->latest('transfer_date')
            ->paginate(12);

        return view('admin.stock-transfers.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('stock-transfers'),
            'icons' => AdminNav::icons(),
            'transfers' => $transfers,
        ]);
    }

    public function create(): View
    {
        return view('admin.stock-transfers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('stock-transfers'),
            'icons' => AdminNav::icons(),
            'transfer' => new StockTransfer([
                'transfer_date' => now()->toDateString(),
                'status' => 'completed',
                'from_location' => 'Main Kitchen',
                'to_location' => 'Storage',
                'transfer_number' => 'ST-'.now()->format('ymd').'-'.str_pad((string) (StockTransfer::count() + 1), 3, '0', STR_PAD_LEFT),
            ]),
            'items' => InventoryItem::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        StockTransfer::create($data);

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer recorded.');
    }

    public function edit(StockTransfer $stock_transfer): View
    {
        return view('admin.stock-transfers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('stock-transfers'),
            'icons' => AdminNav::icons(),
            'transfer' => $stock_transfer,
            'items' => InventoryItem::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, StockTransfer $stock_transfer): RedirectResponse
    {
        $stock_transfer->update($this->validated($request, $stock_transfer->id));

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer updated.');
    }

    public function destroy(StockTransfer $stock_transfer): RedirectResponse
    {
        $stock_transfer->delete();

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'transfer_number' => ['required', 'string', 'max:64', 'unique:stock_transfers,transfer_number,'.($id ?? 'NULL')],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'from_location' => ['required', 'string', 'max:120'],
            'to_location' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'transfer_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
