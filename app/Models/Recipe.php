<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'name', 'code', 'yield_qty', 'yield_unit', 'selling_price', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
        ];
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function foodCost(): float
    {
        return (float) $this->ingredients->sum(function (RecipeIngredient $row) {
            $cost = (float) ($row->inventoryItem?->unit_cost ?? 0);

            return $cost * (float) $row->quantity;
        });
    }
}
