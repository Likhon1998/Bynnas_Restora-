<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(): View
    {
        $transfers = StockTransfer::query()
            ->with(['inventoryItem', 'fromLocation', 'toLocation'])
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
        $locations = Location::query()->where('status', 'active')->orderBy('name')->get();
        $from = $locations->firstWhere('name', 'Main Kitchen') ?? $locations->first();
        $to = $locations->firstWhere('name', 'Storage') ?? $locations->skip(1)->first() ?? $locations->first();

        return view('admin.stock-transfers.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('stock-transfers'),
            'icons' => AdminNav::icons(),
            'transfer' => new StockTransfer([
                'transfer_date' => now()->toDateString(),
                'status' => 'completed',
                'from_location_id' => $from?->id,
                'to_location_id' => $to?->id,
                'transfer_number' => 'ST-'.now()->format('ymd').'-'.str_pad((string) (StockTransfer::count() + 1), 3, '0', STR_PAD_LEFT),
            ]),
            'items' => InventoryItem::orderBy('name')->get(),
            'locations' => $locations,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create($data);
            if ($transfer->status === 'completed') {
                $this->inventory->applyCompletedTransfer($transfer);
            }
        });

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
            'locations' => Location::query()->where('status', 'active')->orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, StockTransfer $stock_transfer): RedirectResponse
    {
        $data = $this->validated($request, $stock_transfer->id);

        DB::transaction(function () use ($stock_transfer, $data) {
            $wasApplied = (bool) $stock_transfer->ledger_applied;
            $becomingCompleted = ($data['status'] ?? null) === 'completed';

            if ($wasApplied) {
                $this->inventory->reverseCompletedTransfer($stock_transfer->fresh());
            }

            $stock_transfer->update($data);

            if ($becomingCompleted) {
                $this->inventory->applyCompletedTransfer($stock_transfer->fresh());
            }
        });

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer updated.');
    }

    public function destroy(StockTransfer $stock_transfer): RedirectResponse
    {
        DB::transaction(function () use ($stock_transfer) {
            if ($stock_transfer->ledger_applied) {
                $this->inventory->reverseCompletedTransfer($stock_transfer);
            }
            $stock_transfer->delete();
        });

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'transfer_number' => ['required', 'string', 'max:64', 'unique:stock_transfers,transfer_number,'.($id ?? 'NULL')],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'from_location_id' => ['required', 'exists:locations,id', 'different:to_location_id'],
            'to_location_id' => ['required', 'exists:locations,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'transfer_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
