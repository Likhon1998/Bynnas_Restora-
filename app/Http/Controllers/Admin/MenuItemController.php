<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        return view('admin.menu-items.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('menu-items'),
            'icons' => AdminNav::icons(),
            'menuItems' => MenuItem::with('recipe')->orderBy('sort_order')->orderBy('name')->get(),
            'categories' => MenuItem::query()->whereNotNull('category')->distinct()->pluck('category'),
        ]);
    }

    public function create(): View
    {
        return view('admin.menu-items.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('menu-items'),
            'icons' => AdminNav::icons(),
            'menuItem' => new MenuItem(['is_available' => true, 'sort_order' => 0]),
            'recipes' => Recipe::query()->where('status', 'active')->orderBy('name')->get(),
            'mode' => 'create',
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        MenuItem::create($this->validated($request));

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item created.');
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('admin.menu-items.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('menu-items'),
            'icons' => AdminNav::icons(),
            'menuItem' => $menuItem,
            'recipes' => Recipe::query()->where('status', 'active')->orderBy('name')->get(),
            'mode' => 'edit',
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($this->validated($request));

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:60'],
            'recipe_id' => ['nullable', 'exists:recipes,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'badge' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'is_favorite' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'is_vegetarian' => ['nullable', 'boolean'],
        ]) + [
            'is_available' => $request->boolean('is_available'),
            'is_favorite' => $request->boolean('is_favorite'),
            'is_bestseller' => $request->boolean('is_bestseller'),
            'is_vegetarian' => $request->boolean('is_vegetarian'),
            'recipe_id' => $request->filled('recipe_id') ? $request->integer('recipe_id') : null,
        ];
    }

    private function categoryOptions(): array
    {
        return [
            'Mains', 'Appetizers', 'Beverages', 'Desserts',
            'Pizza', 'Burger', 'Combo', 'Pasta', 'Soups',
        ];
    }
}
