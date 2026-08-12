<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id', 'location_id', 'type', 'quantity_change', 'unit',
        'unit_cost_snapshot', 'reference_type', 'reference_id', 'notes', 'created_by', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'decimal:3',
            'unit_cost_snapshot' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public const TYPE_PO_RECEIPT = 'po_receipt';

    public const TYPE_POS_SALE = 'pos_sale';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_WASTAGE = 'wastage';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
