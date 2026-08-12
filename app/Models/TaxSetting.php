<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = [
        'tax_name', 'vat_rate', 'service_charge_rate', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->where('is_active', true)->latest('id')->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->firstOrCreate(
            ['tax_name' => 'VAT'],
            [
                'vat_rate' => 7.00,
                'service_charge_rate' => 5.00,
                'is_active' => true,
                'notes' => 'Default restaurant tax & service rates used by POS and reports.',
            ]
        );
    }

    public function vatFraction(): float
    {
        return ((float) $this->vat_rate) / 100;
    }

    public function serviceFraction(): float
    {
        return ((float) $this->service_charge_rate) / 100;
    }
}
