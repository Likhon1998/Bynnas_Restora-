<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class WebMenuController extends Controller
{
    private const CATEGORY_ICONS = [
        'All Items' => 'grid',
        'Mains' => 'main',
        'Main Course' => 'main',
        'Appetizers' => 'appetizer',
        'Starters' => 'appetizer',
        'Beverages' => 'drink',
        'Drinks' => 'drink',
        'Desserts' => 'dessert',
        'Dessert' => 'dessert',
        'Pizza' => 'pizza',
        'Burger' => 'burger',
        'Burgers' => 'burger',
        'Combo' => 'main',
        'Soups' => 'soup',
        'Pasta' => 'pasta',
    ];

    public function index(): JsonResponse
    {
        $items = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = collect(['All Items'])
            ->merge($items->pluck('category')->filter()->unique())
            ->unique()
            ->values()
            ->map(fn (string $label) => [
                'id' => $label === 'All Items' ? 'all' : $this->slug($label),
                'label' => $label,
                'icon' => self::CATEGORY_ICONS[$label] ?? 'grid',
            ]);

        return response()->json([
            'categories' => $categories,
            'items' => $items->map(fn (MenuItem $item) => $this->transformItem($item)),
        ]);
    }

    public function featured(): JsonResponse
    {
        $items = MenuItem::query()
            ->where('is_available', true)
            ->where('is_favorite', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get();

        // Fallback: top available items if nothing is flagged featured yet
        if ($items->isEmpty()) {
            $items = MenuItem::query()
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(4)
                ->get();
        }

        return response()->json([
            'items' => $items->map(fn (MenuItem $item) => $this->transformItem($item))->values(),
        ]);
    }

    private function transformItem(MenuItem $item): array
    {
        $badge = $item->badge ?: ($item->is_bestseller ? 'Bestseller' : ($item->is_favorite ? 'Popular' : null));

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description ?? '',
            'price' => (float) $item->price,
            'category' => $this->slug($item->category ?? 'other'),
            'categoryLabel' => $item->category,
            'badge' => $badge,
            'badgeTone' => $this->badgeTone($badge),
            'image' => $item->image_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=85',
            'isFavorite' => (bool) $item->is_favorite,
            'isBestseller' => (bool) $item->is_bestseller,
            'isVegetarian' => (bool) $item->is_vegetarian,
            'isPopular' => strtolower((string) $badge) === 'popular' || $item->is_bestseller || $item->is_favorite,
            'rating' => $item->rating !== null ? (float) $item->rating : 4.8,
            'reviews' => $item->review_count !== null ? (int) $item->review_count : 100,
        ];
    }

    private function badgeTone(?string $badge): ?string
    {
        return match (strtolower((string) preg_replace('/\s+/', '', (string) $badge))) {
            'popular' => 'orange',
            'bestseller' => 'green',
            'new' => 'blue',
            'spicy', 'hot' => 'red',
            'chefpick', 'chefspick' => 'gold',
            default => 'green',
        };
    }

    private function slug(?string $label): string
    {
        return strtolower(str_replace(' ', '-', trim((string) $label))) ?: 'other';
    }
}
