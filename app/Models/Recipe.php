<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'yield_qty', 'yield_unit', 'selling_price',
        'packaging_cost', 'other_cost', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'packaging_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
        ];
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function foodCost(): float
    {
        return (float) $this->ingredients->sum(function (RecipeIngredient $row) {
            $item = $row->inventoryItem;
            if (! $item) {
                return 0;
            }

            return $item->costForQuantity((float) $row->quantity, $row->unit);
        });
    }

    public function totalCost(): float
    {
        return $this->foodCost() + (float) $this->packaging_cost + (float) $this->other_cost;
    }

    public function costPerPortion(): float
    {
        $yield = max(1, (int) $this->yield_qty);

        return $this->totalCost() / $yield;
    }

    public function profitMargin(): float
    {
        $price = (float) $this->selling_price;
        if ($price <= 0) {
            return 0.0;
        }

        return (($price - $this->costPerPortion()) / $price) * 100;
    }
}
