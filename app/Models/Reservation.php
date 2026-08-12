<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'customer_id', 'guest_name', 'phone', 'guests', 'reserved_at',
        'table_id', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'confirmed', 'seated', 'completed' => 'green',
            'pending' => 'amber',
            'cancelled' => 'red',
            default => 'slate',
        };
    }
}
