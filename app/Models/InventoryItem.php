<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'sku', 'name', 'category', 'unit', 'quantity_on_hand', 'reorder_level',
        'unit_cost', 'supplier_id', 'location', 'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
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
}
