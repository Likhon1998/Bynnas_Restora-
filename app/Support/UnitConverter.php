<?php

namespace App\Support;

/**
 * Convert quantities between compatible units for BOM costing and stock.
 *
 * Inventory unit_cost is always per the item's stock/purchase unit (e.g. kg, L).
 * Recipe lines may use smaller units (g, ml) — convert before cost / deduction.
 */
class UnitConverter
{
    /** @var array<string, array{family: string, to_base: float}> */
    private const UNITS = [
        'kg' => ['family' => 'mass', 'to_base' => 1000],
        'kilogram' => ['family' => 'mass', 'to_base' => 1000],
        'kilograms' => ['family' => 'mass', 'to_base' => 1000],
        'g' => ['family' => 'mass', 'to_base' => 1],
        'gram' => ['family' => 'mass', 'to_base' => 1],
        'grams' => ['family' => 'mass', 'to_base' => 1],
        'mg' => ['family' => 'mass', 'to_base' => 0.001],
        'l' => ['family' => 'volume', 'to_base' => 1000],
        'liter' => ['family' => 'volume', 'to_base' => 1000],
        'litre' => ['family' => 'volume', 'to_base' => 1000],
        'liters' => ['family' => 'volume', 'to_base' => 1000],
        'litres' => ['family' => 'volume', 'to_base' => 1000],
        'ml' => ['family' => 'volume', 'to_base' => 1],
        'milliliter' => ['family' => 'volume', 'to_base' => 1],
        'millilitre' => ['family' => 'volume', 'to_base' => 1],
        'pcs' => ['family' => 'count', 'to_base' => 1],
        'pc' => ['family' => 'count', 'to_base' => 1],
        'piece' => ['family' => 'count', 'to_base' => 1],
        'pieces' => ['family' => 'count', 'to_base' => 1],
        'unit' => ['family' => 'count', 'to_base' => 1],
        'portion' => ['family' => 'count', 'to_base' => 1],
        'plate' => ['family' => 'count', 'to_base' => 1],
        'serving' => ['family' => 'count', 'to_base' => 1],
        'cup' => ['family' => 'volume', 'to_base' => 240],
        'tbsp' => ['family' => 'volume', 'to_base' => 15],
        'tsp' => ['family' => 'volume', 'to_base' => 5],
    ];

    public static function normalize(?string $unit): string
    {
        $unit = strtolower(trim((string) $unit));

        return match ($unit) {
            'lt' => 'l',
            default => $unit,
        };
    }

    public static function canConvert(?string $from, ?string $to): bool
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        if ($from === '' || $to === '' || $from === $to) {
            return true;
        }

        $a = self::UNITS[$from] ?? null;
        $b = self::UNITS[$to] ?? null;

        return $a && $b && $a['family'] === $b['family'];
    }

    /**
     * Convert $quantity from $fromUnit into $toUnit.
     * Returns null when units are incompatible.
     */
    public static function convert(float $quantity, ?string $fromUnit, ?string $toUnit): ?float
    {
        $from = self::normalize($fromUnit);
        $to = self::normalize($toUnit);

        if ($from === '' || $to === '' || $from === $to) {
            return $quantity;
        }

        $a = self::UNITS[$from] ?? null;
        $b = self::UNITS[$to] ?? null;

        if (! $a || ! $b || $a['family'] !== $b['family']) {
            return null;
        }

        $inBase = $quantity * $a['to_base'];

        return $b['to_base'] > 0 ? $inBase / $b['to_base'] : null;
    }
}
