<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WastageRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_item_id', 'quantity', 'reason', 'type', 'cost_impact', 'recorded_on', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'cost_impact' => 'decimal:2',
            'recorded_on' => 'date',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
