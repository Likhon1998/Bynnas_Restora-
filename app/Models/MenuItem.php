<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'name', 'category', 'description', 'price', 'is_available', 'image_url',
        'badge', 'is_favorite', 'is_bestseller', 'is_vegetarian', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_favorite' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_vegetarian' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
