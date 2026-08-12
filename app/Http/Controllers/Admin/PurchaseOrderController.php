<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->get('status');

        $orders = PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('order_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.purchase-orders.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('purchase-orders'),
            'icons' => AdminNav::icons(),
            'orders' => $orders,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): View
    {
        return view('admin.purchase-orders.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('purchase-orders'),
            'icons' => AdminNav::icons(),
            'order' => new PurchaseOrder([
                'order_date' => now()->toDateString(),
                'status' => 'draft',
                'po_number' => 'PO-'.now()->format('ymd').'-'.str_pad((string) (PurchaseOrder::count() + 1), 3, '0', STR_PAD_LEFT),
            ]),
            'suppliers' => Supplier::where('status', 'active')->orderBy('name')->get(),
            'items' => InventoryItem::orderBy('name')->get(),
            'lineRows' => [['inventory_item_id' => '', 'quantity' => '', 'unit_cost' => '']],
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $order = PurchaseOrder::create(collect($data)->except('lines')->all());
            $total = $this->syncLines($order, $data['lines'] ?? []);
            $order->update(['total_amount' => $total]);

            if ($data['status'] === 'received') {
                $this->receiveStock($order->fresh('items'));
            }
        });

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function edit(PurchaseOrder $purchase_order): View
    {
        $purchase_order->load('items');

        $rows = $purchase_order->items->map(fn ($row) => [
            'inventory_item_id' => $row->inventory_item_id,
            'quantity' => $row->quantity,
            'unit_cost' => $row->unit_cost,
        ])->values()->all();

        if ($rows === []) {
            $rows = [['inventory_item_id' => '', 'quantity' => '', 'unit_cost' => '']];
        }

        return view('admin.purchase-orders.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('purchase-orders'),
            'icons' => AdminNav::icons(),
            'order' => $purchase_order,
            'suppliers' => Supplier::orderBy('name')->get(),
            'items' => InventoryItem::orderBy('name')->get(),
            'lineRows' => $rows,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchase_order): RedirectResponse
    {
        $data = $this->validated($request, $purchase_order->id);
        $oldStatus = $purchase_order->status;

        DB::transaction(function () use ($purchase_order, $data, $oldStatus) {
            $purchase_order->update(collect($data)->except('lines')->all());
            $purchase_order->items()->delete();
            $total = $this->syncLines($purchase_order, $data['lines'] ?? []);
            $purchase_order->update(['total_amount' => $total]);

            if ($oldStatus !== 'received' && $data['status'] === 'received') {
                $this->receiveStock($purchase_order->fresh('items'));
            }
        });

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchase_order): RedirectResponse
    {
        $purchase_order->delete();

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase order deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'po_number' => ['required', 'string', 'max:64', 'unique:purchase_orders,po_number,'.($id ?? 'NULL')],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,sent,partial,received,cancelled'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function syncLines(PurchaseOrder $order, array $lines): float
    {
        $total = 0;
        foreach ($lines as $row) {
            if (empty($row['inventory_item_id'])) {
                continue;
            }
            $qty = (float) $row['quantity'];
            $cost = (float) $row['unit_cost'];
            $total += $qty * $cost;
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'inventory_item_id' => $row['inventory_item_id'],
                'quantity' => $qty,
                'unit_cost' => $cost,
                'received_qty' => $order->status === 'received' ? $qty : 0,
            ]);
        }

        return $total;
    }

    private function receiveStock(PurchaseOrder $order): void
    {
        foreach ($order->items as $line) {
            $item = InventoryItem::query()->find($line->inventory_item_id);
            if (! $item) {
                continue;
            }

            $qty = (float) $line->quantity;
            $purchaseCost = (float) $line->unit_cost;

            // WAC must run against on-hand qty/cost BEFORE the receipt hits the ledger.
            $this->inventory->recalculateUnitCost($item, $qty, $purchaseCost);

            $locationId = $this->inventory->resolveLocationId($item);

            $this->inventory->recordTransaction(
                inventoryItemId: $item->id,
                locationId: $locationId,
                type: InventoryTransaction::TYPE_PO_RECEIPT,
                quantityChange: $qty,
                referenceType: PurchaseOrder::class,
                referenceId: $order->id,
                notes: "PO {$order->po_number} receipt",
            );

            $line->update(['received_qty' => $line->quantity]);
        }
    }
}
