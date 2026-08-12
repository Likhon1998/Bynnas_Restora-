<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'title', 'category', 'amount', 'expense_date', 'payment_method',
        'vendor', 'reference', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function categories(): array
    {
        return [
            'rent' => 'Rent',
            'utilities' => 'Utilities',
            'salaries' => 'Salaries & Wages',
            'supplies' => 'Kitchen Supplies',
            'marketing' => 'Marketing',
            'maintenance' => 'Maintenance',
            'transport' => 'Transport',
            'packaging' => 'Packaging',
            'other' => 'Other',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categories()[$this->category] ?? ucfirst((string) $this->category);
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_method) {
            'card' => 'Card',
            'bank' => 'Bank Transfer',
            'online' => 'Online',
            default => 'Cash',
        };
    }
}
