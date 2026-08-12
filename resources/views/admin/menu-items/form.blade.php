@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Menu Item' : 'Edit Menu Item')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Menu Item' : 'Edit Menu Item' }}</h1>
        <p>Changes sync to POS and the public menu page instantly.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.menu-items.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.menu-items.store') : route('admin.menu-items.update', $menuItem) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Name<input class="field" name="name" value="{{ old('name', $menuItem->name) }}" required></label>
            <label>Category
                <input class="field" name="category" list="categoryList" value="{{ old('category', $menuItem->category) }}" placeholder="e.g. Mains">
                <datalist id="categoryList">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </label>
            <label>Price (৳)<input class="field" type="number" step="0.01" min="0" name="price" value="{{ old('price', $menuItem->price) }}" required></label>
            <label>Sort Order<input class="field" type="number" min="0" name="sort_order" value="{{ old('sort_order', $menuItem->sort_order) }}"></label>
            <label>Badge
                <select class="field" name="badge">
                    <option value="">None</option>
                    @foreach (['Best Seller', 'Popular', 'Bestseller', 'Hot', 'Chef Pick', 'New', 'Spicy'] as $badge)
                        <option value="{{ $badge }}" @selected(old('badge', $menuItem->badge) === $badge)>{{ $badge }}</option>
                    @endforeach
                </select>
            </label>
            <label>Image URL<input class="field" name="image_url" value="{{ old('image_url', $menuItem->image_url) }}" placeholder="https://..."></label>
            <label>Rating (0–5)<input class="field" type="number" step="0.1" min="0" max="5" name="rating" value="{{ old('rating', $menuItem->rating) }}" placeholder="4.9"></label>
            <label>Review count<input class="field" type="number" min="0" name="review_count" value="{{ old('review_count', $menuItem->review_count) }}" placeholder="120"></label>
        </div>
        <label style="display:block;margin-top:12px">Ingredients / Description
            <textarea class="field" name="description" rows="3" style="height:auto;padding:10px 12px" placeholder="e.g. Salmon, lemon butter, seasonal greens">{{ old('description', $menuItem->description) }}</textarea>
        </label>
        <div class="form-grid" style="margin-top:12px">
            <label><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $menuItem->is_available))> Available on POS & website</label>
            <label><input type="checkbox" name="is_favorite" value="1" @checked(old('is_favorite', $menuItem->is_favorite))> Show on homepage (Featured)</label>
            <label><input type="checkbox" name="is_bestseller" value="1" @checked(old('is_bestseller', $menuItem->is_bestseller))> Bestseller</label>
            <label><input type="checkbox" name="is_vegetarian" value="1" @checked(old('is_vegetarian', $menuItem->is_vegetarian))> Vegetarian</label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Item</button></div>
    </form>
</section>
@endsection
