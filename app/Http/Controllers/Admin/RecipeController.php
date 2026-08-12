<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $recipes = Recipe::query()
            ->with(['ingredients.inventoryItem'])
            ->withCount('ingredients')
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'ilike', "%{$q}%")->orWhere('code', 'ilike', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.recipes.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('recipes'),
            'icons' => AdminNav::icons(),
            'recipes' => $recipes,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): View
    {
        return view('admin.recipes.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('recipes'),
            'icons' => AdminNav::icons(),
            'recipe' => new Recipe(['yield_qty' => 1, 'yield_unit' => 'portion', 'status' => 'active']),
            'items' => InventoryItem::orderBy('name')->get(),
            'ingredientRows' => [['inventory_item_id' => '', 'quantity' => '', 'unit' => '']],
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $recipe = Recipe::create(collect($data)->except('ingredients')->all());
            $this->syncIngredients($recipe, $data['ingredients'] ?? []);
        });

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created.');
    }

    public function edit(Recipe $recipe): View
    {
        $recipe->load('ingredients');

        $rows = $recipe->ingredients->map(fn ($row) => [
            'inventory_item_id' => $row->inventory_item_id,
            'quantity' => $row->quantity,
            'unit' => $row->unit,
        ])->values()->all();

        if ($rows === []) {
            $rows = [['inventory_item_id' => '', 'quantity' => '', 'unit' => '']];
        }

        return view('admin.recipes.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('recipes'),
            'icons' => AdminNav::icons(),
            'recipe' => $recipe,
            'items' => InventoryItem::orderBy('name')->get(),
            'ingredientRows' => $rows,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $data = $this->validated($request, $recipe->id);

        DB::transaction(function () use ($recipe, $data) {
            $recipe->update(collect($data)->except('ingredients')->all());
            $recipe->ingredients()->delete();
            $this->syncIngredients($recipe, $data['ingredients'] ?? []);
        });

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated.');
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:64', 'unique:recipes,code,'.($id ?? 'NULL')],
            'yield_qty' => ['required', 'integer', 'min:1'],
            'yield_unit' => ['required', 'string', 'max:32'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:32'],
        ]);
    }

    private function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        foreach ($ingredients as $row) {
            if (empty($row['inventory_item_id'])) {
                continue;
            }
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'inventory_item_id' => $row['inventory_item_id'],
                'quantity' => $row['quantity'],
                'unit' => $row['unit'] ?: null,
            ]);
        }
    }
}
