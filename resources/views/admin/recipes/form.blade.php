@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Recipe' : 'Edit Recipe')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Recipe' : 'Edit Recipe' }}</h1>
        <p>Define ingredient quantities for accurate food cost.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.recipes.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.recipes.store') : route('admin.recipes.update', $recipe) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Name<input class="field" name="name" value="{{ old('name', $recipe->name) }}" required></label>
            <label>Code<input class="field" name="code" value="{{ old('code', $recipe->code) }}"></label>
            <label>Yield qty<input class="field" type="number" name="yield_qty" value="{{ old('yield_qty', $recipe->yield_qty) }}" required></label>
            <label>Yield unit<input class="field" name="yield_unit" value="{{ old('yield_unit', $recipe->yield_unit) }}" required></label>
            <label>Selling price<input class="field" type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $recipe->selling_price) }}" required></label>
            <label>Status
                <select class="field" name="status">
                    <option value="active" @selected(old('status', $recipe->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status', $recipe->status)==='inactive')>Inactive</option>
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $recipe->notes) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:18px 0 10px">Ingredients</h3>
        <div id="ingredientRows" class="line-rows">
            @foreach (old('ingredients', $ingredientRows) as $i => $row)
                <div class="line-row">
                    <select class="field" name="ingredients[{{ $i }}][inventory_item_id]" required>
                        <option value="">Select item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected((string)($row['inventory_item_id'] ?? '') === (string)$item->id)>{{ $item->name }} ({{ $item->unit }})</option>
                        @endforeach
                    </select>
                    <input class="field" type="number" step="0.001" name="ingredients[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? '' }}" placeholder="Qty" required>
                    <input class="field" name="ingredients[{{ $i }}][unit]" value="{{ $row['unit'] ?? '' }}" placeholder="Unit">
                </div>
            @endforeach
        </div>
        <button type="button" class="btn" id="addIngredient" style="margin-top:8px">+ Add ingredient</button>

        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Recipe</button></div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('ingredientRows');
    var btn = document.getElementById('addIngredient');
    if (!wrap || !btn) return;
    var options = `@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>@endforeach`;
    btn.addEventListener('click', function () {
        var i = wrap.children.length;
        var div = document.createElement('div');
        div.className = 'line-row';
        div.innerHTML = '<select class="field" name="ingredients['+i+'][inventory_item_id]" required><option value="">Select item</option>'+options+'</select>'
            + '<input class="field" type="number" step="0.001" name="ingredients['+i+'][quantity]" placeholder="Qty" required>'
            + '<input class="field" name="ingredients['+i+'][unit]" placeholder="Unit">';
        wrap.appendChild(div);
    });
})();
</script>
@endpush
