@extends('admin.layouts.app')

@section('title', 'Recipes (BOM)')

@section('content')
<div class="page-head">
    <div>
        <h1>Recipes (BOM)</h1>
        <p>Bill of materials linked to live inventory costs.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.recipes.create') }}" class="btn btn-gold">+ Add Recipe</a>
    </div>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search recipes...">
        <button class="btn btn-solid" type="submit">Search</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Recipe</th>
                    <th>Yield</th>
                    <th>Ingredients</th>
                    <th>Food cost</th>
                    <th>Cost / portion</th>
                    <th>Sell price</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recipes as $recipe)
                    @php
                        $cost = $recipe->foodCost();
                        $per = $recipe->costPerPortion();
                        $margin = $recipe->profitMargin();
                        $statusTone = match ($recipe->status) {
                            'active' => 'green',
                            'draft' => 'amber',
                            default => 'slate',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $recipe->name }}</strong><small class="muted">{{ $recipe->code }}</small></td>
                        <td>{{ $recipe->yield_qty }} {{ $recipe->yield_unit }}</td>
                        <td>{{ $recipe->ingredients_count }}</td>
                        <td>৳ {{ number_format($cost, 2) }}</td>
                        <td>
                            ৳ {{ number_format($per, 2) }}
                            <small class="muted">{{ number_format($margin, 1) }}% margin</small>
                        </td>
                        <td>৳ {{ number_format((float)$recipe->selling_price, 2) }}</td>
                        <td><span class="pill {{ $statusTone }}">{{ ucfirst($recipe->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.recipes.edit', $recipe) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.recipes.destroy', $recipe) }}" onsubmit="return confirm('Delete recipe?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No recipes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $recipes->links('pagination.admin') }}</div>
</section>
@endsection
