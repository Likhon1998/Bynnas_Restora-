<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        return $this->formView(new Recipe([
            'yield_qty' => 1,
            'yield_unit' => 'portion',
            'status' => 'active',
            'packaging_cost' => 0,
            'other_cost' => 0,
            'selling_price' => 0,
        ]), 'create', [['inventory_item_id' => '', 'quantity' => '', 'unit' => '']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->input('action') === 'draft') {
            $data['status'] = 'draft';
        }

        DB::transaction(function () use ($data) {
            $recipe = Recipe::create(collect($data)->except('ingredients')->all());
            $this->syncIngredients($recipe, $data['ingredients'] ?? []);
        });

        $msg = ($data['status'] ?? '') === 'draft' ? 'Recipe saved as draft.' : 'Recipe created.';

        return redirect()->route('admin.recipes.index')->with('success', $msg);
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

        return $this->formView($recipe, 'edit', $rows);
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $data = $this->validated($request, $recipe->id);

        if ($request->input('action') === 'draft') {
            $data['status'] = 'draft';
        }

        DB::transaction(function () use ($recipe, $data) {
            $recipe->update(collect($data)->except('ingredients')->all());
            $recipe->ingredients()->delete();
            $this->syncIngredients($recipe, $data['ingredients'] ?? []);
        });

        $msg = ($data['status'] ?? '') === 'draft' ? 'Recipe saved as draft.' : 'Recipe updated.';

        return redirect()->route('admin.recipes.index')->with('success', $msg);
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted.');
    }

    public function checkName(Request $request): JsonResponse
    {
        $name = trim((string) $request->get('name', ''));
        $exceptId = (int) $request->get('except', 0);

        if ($name === '') {
            return response()->json(['exists' => false, 'recipes' => []]);
        }

        $recipes = Recipe::query()
            ->when($exceptId > 0, fn ($query) => $query->whereKeyNot($exceptId))
            ->where('name', 'ilike', $name)
            ->orderBy('code')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'exists' => $recipes->isNotEmpty(),
            'recipes' => $recipes->map(fn (Recipe $recipe) => [
                'id' => $recipe->id,
                'name' => $recipe->name,
                'code' => $recipe->code,
            ])->values(),
        ]);
    }

    private function formView(Recipe $recipe, string $mode, array $ingredientRows): View
    {
        $items = InventoryItem::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'purchase_unit', 'base_unit', 'conversion_rate', 'unit_cost', 'sku']);

        $importRecipes = Recipe::query()
            ->with(['ingredients:id,recipe_id,inventory_item_id,quantity,unit'])
            ->when($mode === 'edit' && $recipe->id, fn ($q) => $q->whereKeyNot($recipe->id))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('admin.recipes.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('recipes'),
            'icons' => AdminNav::icons(),
            'recipe' => $recipe,
            'items' => $items,
            'importRecipes' => $importRecipes,
            'ingredientRows' => $ingredientRows,
            'mode' => $mode,
            'inventoryPayload' => $items->map(fn (InventoryItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->costUnit(),
                'unit_cost' => (float) $item->unit_cost,
                'sku' => $item->sku,
            ])->values(),
            'importPayload' => $importRecipes->map(fn (Recipe $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'code' => $r->code,
                'ingredients' => $r->ingredients->map(fn ($ing) => [
                    'inventory_item_id' => $ing->inventory_item_id,
                    'quantity' => (float) $ing->quantity,
                    'unit' => $ing->unit,
                ])->values(),
            ])->values(),
        ]);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:64', 'unique:recipes,code,'.($id ?? 'NULL')],
            'yield_qty' => ['required', 'integer', 'min:1'],
            'yield_unit' => ['required', 'string', 'max:32'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'packaging_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,draft'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:32'],
        ]);

        $data['packaging_cost'] = (float) ($data['packaging_cost'] ?? 0);
        $data['other_cost'] = (float) ($data['other_cost'] ?? 0);

        return $data;
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
