<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'category', 'unit', 'base_unit', 'purchase_unit', 'conversion_rate',
        'quantity_on_hand', 'reorder_level', 'unit_cost', 'supplier_id', 'location',
        'default_location_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'conversion_rate' => 'decimal:6',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'default_location_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'inventory_item_location')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity_on_hand <= (float) $this->reorder_level;
    }

    public function stockPercent(): int
    {
        $min = (float) $this->reorder_level;
        if ($min <= 0) {
            return 100;
        }

        return (int) min(100, round(((float) $this->quantity_on_hand / $min) * 100));
    }

    /** Convert purchase-unit qty into base units (recipe/ledger). */
    public function toBaseUnits(float $purchaseQty): float
    {
        return $purchaseQty * (float) ($this->conversion_rate ?: 1);
    }

    /** Convert base-unit qty into purchase units. */
    public function toPurchaseUnits(float $baseQty): float
    {
        $rate = (float) ($this->conversion_rate ?: 1);

        return $rate > 0 ? $baseQty / $rate : $baseQty;
    }
}
