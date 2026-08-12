<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'type', 'status', 'table_id', 'customer_id', 'customer_name',
        'meta', 'guest_count', 'subtotal', 'service_charge', 'tax_amount', 'tip_amount',
        'promo_code', 'discount_amount', 'total', 'payment_status', 'is_held',
        'notes', 'tags', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'is_held' => 'boolean',
            'tags' => 'array',
            'placed_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'ready', 'completed' => 'green',
            'preparing', 'pending' => 'amber',
            'on_the_way' => 'blue',
            'cancelled' => 'red',
            default => 'slate',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'on_the_way' => 'On the Way',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'dinein' => 'Dine-in',
            'takeaway' => 'Takeaway',
            'delivery' => 'Delivery',
            'walkin' => 'Walk-in',
            'qr' => 'QR Order',
            default => ucfirst($this->type),
        };
    }
}
