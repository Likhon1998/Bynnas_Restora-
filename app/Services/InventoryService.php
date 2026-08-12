<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
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
