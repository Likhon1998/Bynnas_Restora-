<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Order;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Single write-path for stock mutations.
 *
 * Controllers must never set quantity_on_hand or pivot quantity directly.
 * All changes go through inventory_transactions; cached quantities are derived.
 *
 * Steps 1–2 note: $quantityChange is recorded as provided (same unit as current
 * quantity_on_hand / PO lines). Step 3 will enforce base_unit conversion at the edge.
 */
class InventoryService
{
    /**
     * Append an immutable ledger row, then refresh cached global + per-location qty.
     *
     * @param  float  $quantityChange  Signed delta (positive = in, negative = out)
     */
    public function recordTransaction(
        int $inventoryItemId,
        int $locationId,
        string $type,
        float $quantityChange,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
    ): InventoryTransaction {
        $this->assertValidType($type);

        if (! Location::query()->whereKey($locationId)->exists()) {
            throw new InvalidArgumentException("Location [{$locationId}] does not exist.");
        }

        return DB::transaction(function () use (
            $inventoryItemId,
            $locationId,
            $type,
            $quantityChange,
            $referenceType,
            $referenceId,
            $notes,
        ) {
            /** @var InventoryItem $item */
            $item = InventoryItem::query()
                ->whereKey($inventoryItemId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureOpeningBalanceIfNeeded($item, $locationId);

            $transaction = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'location_id' => $locationId,
                'type' => $type,
                'quantity_change' => $quantityChange,
                'unit' => $item->base_unit ?: $item->unit,
                'unit_cost_snapshot' => $item->unit_cost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            $globalQty = (float) InventoryTransaction::query()
                ->where('inventory_item_id', $item->id)
                ->sum('quantity_change');

            $item->quantity_on_hand = $globalQty;
            $item->save();

            $locationQty = (float) InventoryTransaction::query()
                ->where('inventory_item_id', $item->id)
                ->where('location_id', $locationId)
                ->sum('quantity_change');

            $item->locations()->syncWithoutDetaching([
                $locationId => ['quantity' => $locationQty],
            ]);

            return $transaction;
        });
    }

    /**
     * Weighted Average Cost before a purchase receipt is posted to the ledger.
     *
     * Formula:
     * ((currentQty * currentCost) + (newQty * newCost)) / (currentQty + newQty)
     *
     * Qty/cost are currently in purchase/legacy units (same as quantity_on_hand / unit_cost).
     * Step 3 will switch this to base-unit math via conversion_rate.
     */
    public function recalculateUnitCost(
        InventoryItem $item,
        float $newPurchaseQty,
        float $newPurchaseUnitCost,
    ): float {
        if ($newPurchaseQty <= 0) {
            return (float) $item->unit_cost;
        }

        $currentQty = (float) $item->quantity_on_hand;
        $currentCost = (float) $item->unit_cost;

        if ($currentQty <= 0) {
            $wac = $newPurchaseUnitCost;
        } else {
            $wac = (
                ($currentQty * $currentCost) + ($newPurchaseQty * $newPurchaseUnitCost)
            ) / ($currentQty + $newPurchaseQty);
        }

        $item->unit_cost = round($wac, 2);
        $item->save();

        return (float) $item->unit_cost;
    }

    /**
     * Resolve the location that should receive stock for an item.
     */
    public function resolveLocationId(InventoryItem $item, ?int $preferredLocationId = null): int
    {
        if ($preferredLocationId) {
            return $preferredLocationId;
        }

        if ($item->default_location_id) {
            return (int) $item->default_location_id;
        }

        $fallback = Location::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id');

        if (! $fallback) {
            throw new RuntimeException('No active inventory location is configured.');
        }

        return (int) $fallback;
    }

    /**
     * Deduct recipe ingredients for a sold/committed order (idempotent).
     * Menu lines without a recipe are skipped.
     */
    public function consumeOrder(Order $order): void
    {
        if ($order->inventory_deducted) {
            return;
        }

        $order->loadMissing(['items.menuItem.recipe.ingredients.inventoryItem']);

        $requirements = $this->aggregateOrderRequirements($order);
        if ($requirements === []) {
            $order->forceFill(['inventory_deducted' => true])->save();

            return;
        }

        $shortages = [];
        foreach ($requirements as $row) {
            /** @var InventoryItem $item */
            $item = $row['item'];
            $need = (float) $row['quantity'];
            $onHand = (float) $item->quantity_on_hand;
            if ($onHand + 0.0005 < $need) {
                $unit = $item->base_unit ?: $item->unit;
                $shortages[] = sprintf(
                    '%s needs %s %s (on hand %s)',
                    $item->name,
                    rtrim(rtrim(number_format($need, 3, '.', ''), '0'), '.'),
                    $unit,
                    rtrim(rtrim(number_format($onHand, 3, '.', ''), '0'), '.')
                );
            }
        }

        if ($shortages !== []) {
            throw ValidationException::withMessages([
                'inventory' => 'Insufficient stock: '.implode('; ', $shortages),
            ]);
        }

        DB::transaction(function () use ($order, $requirements) {
            foreach ($requirements as $row) {
                /** @var InventoryItem $item */
                $item = $row['item'];
                $qty = (float) $row['quantity'];
                $locationId = $this->resolveLocationId($item);

                $this->recordTransaction(
                    inventoryItemId: $item->id,
                    locationId: $locationId,
                    type: InventoryTransaction::TYPE_POS_SALE,
                    quantityChange: -abs($qty),
                    referenceType: Order::class,
                    referenceId: $order->id,
                    notes: 'Sale '.$order->order_number,
                );
            }

            $order->forceFill(['inventory_deducted' => true])->save();
        });
    }

    /**
     * Restore stock previously deducted for an order (idempotent).
     */
    public function restoreOrder(Order $order): void
    {
        if (! $order->inventory_deducted) {
            return;
        }

        DB::transaction(function () use ($order) {
            $sales = InventoryTransaction::query()
                ->where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', InventoryTransaction::TYPE_POS_SALE)
                ->where('quantity_change', '<', 0)
                ->get();

            foreach ($sales as $sale) {
                $alreadyRestored = InventoryTransaction::query()
                    ->where('reference_type', Order::class)
                    ->where('reference_id', $order->id)
                    ->where('type', InventoryTransaction::TYPE_ADJUSTMENT)
                    ->where('inventory_item_id', $sale->inventory_item_id)
                    ->where('notes', 'like', 'Reversal:%')
                    ->exists();

                if ($alreadyRestored) {
                    continue;
                }

                $this->recordTransaction(
                    inventoryItemId: (int) $sale->inventory_item_id,
                    locationId: (int) $sale->location_id,
                    type: InventoryTransaction::TYPE_ADJUSTMENT,
                    quantityChange: abs((float) $sale->quantity_change),
                    referenceType: Order::class,
                    referenceId: $order->id,
                    notes: 'Reversal: cancelled '.$order->order_number,
                );
            }

            $order->forceFill(['inventory_deducted' => false])->save();
        });
    }

    /**
     * Move stock between locations for a completed transfer (idempotent).
     */
    public function applyCompletedTransfer(StockTransfer $transfer): void
    {
        if ($transfer->status !== 'completed') {
            return;
        }

        if ($transfer->ledger_applied) {
            return;
        }

        $qty = abs((float) $transfer->quantity);
        if ($qty < 0.0005) {
            return;
        }

        if ((int) $transfer->from_location_id === (int) $transfer->to_location_id) {
            throw ValidationException::withMessages([
                'to_location_id' => 'From and to locations must be different.',
            ]);
        }

        $item = InventoryItem::query()->findOrFail($transfer->inventory_item_id);

        $hasLedger = InventoryTransaction::query()
            ->where('inventory_item_id', $item->id)
            ->exists();

        if ($hasLedger) {
            $fromQty = (float) InventoryTransaction::query()
                ->where('inventory_item_id', $item->id)
                ->where('location_id', $transfer->from_location_id)
                ->sum('quantity_change');
        } else {
            $defaultId = $item->default_location_id
                ? (int) $item->default_location_id
                : $this->resolveLocationId($item);
            $fromQty = ((int) $transfer->from_location_id === $defaultId)
                ? (float) $item->quantity_on_hand
                : 0.0;
        }

        if ($fromQty + 0.0005 < $qty) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Insufficient stock at source location (available %s).',
                    rtrim(rtrim(number_format($fromQty, 3, '.', ''), '0'), '.')
                ),
            ]);
        }

        DB::transaction(function () use ($transfer, $qty) {
            $this->recordTransaction(
                inventoryItemId: (int) $transfer->inventory_item_id,
                locationId: (int) $transfer->from_location_id,
                type: InventoryTransaction::TYPE_TRANSFER_OUT,
                quantityChange: -$qty,
                referenceType: StockTransfer::class,
                referenceId: $transfer->id,
                notes: 'Transfer out '.$transfer->transfer_number,
            );

            $this->recordTransaction(
                inventoryItemId: (int) $transfer->inventory_item_id,
                locationId: (int) $transfer->to_location_id,
                type: InventoryTransaction::TYPE_TRANSFER_IN,
                quantityChange: $qty,
                referenceType: StockTransfer::class,
                referenceId: $transfer->id,
                notes: 'Transfer in '.$transfer->transfer_number,
            );

            $transfer->forceFill(['ledger_applied' => true])->save();
        });
    }

    /**
     * Undo ledger rows for a completed transfer (idempotent).
     */
    public function reverseCompletedTransfer(StockTransfer $transfer): void
    {
        if (! $transfer->ledger_applied) {
            return;
        }

        DB::transaction(function () use ($transfer) {
            $rows = InventoryTransaction::query()
                ->where('reference_type', StockTransfer::class)
                ->where('reference_id', $transfer->id)
                ->whereIn('type', [
                    InventoryTransaction::TYPE_TRANSFER_IN,
                    InventoryTransaction::TYPE_TRANSFER_OUT,
                ])
                ->orderByDesc('id')
                ->limit(2)
                ->get();

            // Reverse the latest in/out pair for this transfer.
            foreach ($rows as $row) {
                $this->recordTransaction(
                    inventoryItemId: (int) $row->inventory_item_id,
                    locationId: (int) $row->location_id,
                    type: InventoryTransaction::TYPE_ADJUSTMENT,
                    quantityChange: -1 * (float) $row->quantity_change,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                    notes: 'Reversal: '.$transfer->transfer_number,
                );
            }

            $transfer->forceFill(['ledger_applied' => false])->save();
        });
    }

    /**
     * Post opening stock for a newly created item.
     */
    public function postOpeningBalance(InventoryItem $item, float $quantity, ?int $locationId = null): void
    {
        $qty = abs($quantity);
        if ($qty < 0.0005) {
            return;
        }

        $locationId = $this->resolveLocationId($item, $locationId);

        $this->recordTransaction(
            inventoryItemId: $item->id,
            locationId: $locationId,
            type: InventoryTransaction::TYPE_ADJUSTMENT,
            quantityChange: $qty,
            referenceType: InventoryItem::class,
            referenceId: $item->id,
            notes: 'Opening balance',
        );
    }

    /**
     * @return array<int, array{item: InventoryItem, quantity: float}>
     */
    private function aggregateOrderRequirements(Order $order): array
    {
        $needs = [];

        foreach ($order->items as $line) {
            $menu = $line->menuItem;
            $recipe = $menu?->recipe;
            if (! $recipe || $recipe->status !== 'active') {
                continue;
            }

            $yield = max(1, (int) $recipe->yield_qty);
            $portions = (int) $line->quantity;
            $factor = $portions / $yield;

            foreach ($recipe->ingredients as $ingredient) {
                $item = $ingredient->inventoryItem;
                if (! $item) {
                    continue;
                }

                $qty = $item->toCostUnits((float) $ingredient->quantity, $ingredient->unit) * $factor;
                if ($qty < 0.0005) {
                    continue;
                }

                if (! isset($needs[$item->id])) {
                    $needs[$item->id] = ['item' => $item, 'quantity' => 0.0];
                }
                $needs[$item->id]['quantity'] += $qty;
            }
        }

        return $needs;
    }

    /**
     * Preserve pre-ledger on-hand when the first mutation hits an item.
     */
    private function ensureOpeningBalanceIfNeeded(InventoryItem $item, int $fallbackLocationId): void
    {
        $hasLedger = InventoryTransaction::query()
            ->where('inventory_item_id', $item->id)
            ->exists();

        if ($hasLedger) {
            return;
        }

        $qty = (float) $item->quantity_on_hand;
        if (abs($qty) < 0.0005) {
            return;
        }

        $locationId = $item->default_location_id
            ? (int) $item->default_location_id
            : $fallbackLocationId;

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'location_id' => $locationId,
            'type' => InventoryTransaction::TYPE_ADJUSTMENT,
            'quantity_change' => $qty,
            'unit' => $item->base_unit ?: $item->unit,
            'unit_cost_snapshot' => $item->unit_cost,
            'notes' => 'Opening balance (pre-ledger cutover)',
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    private function assertValidType(string $type): void
    {
        $allowed = [
            InventoryTransaction::TYPE_PO_RECEIPT,
            InventoryTransaction::TYPE_POS_SALE,
            InventoryTransaction::TYPE_TRANSFER_IN,
            InventoryTransaction::TYPE_TRANSFER_OUT,
            InventoryTransaction::TYPE_WASTAGE,
            InventoryTransaction::TYPE_ADJUSTMENT,
        ];

        if (! in_array($type, $allowed, true)) {
            throw new InvalidArgumentException("Invalid inventory transaction type [{$type}].");
        }
    }
}
