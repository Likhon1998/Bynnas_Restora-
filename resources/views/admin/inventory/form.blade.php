@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Inventory Item' : 'Edit Inventory Item')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Inventory Item' : 'Edit Inventory Item' }}</h1>
        <p>Keep stock, cost, and reorder levels accurate.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.inventory.index') }}" class="btn">Back</a>
    </div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.inventory.store') : route('admin.inventory.update', $item) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif

        <div class="form-grid">
            <label>SKU<input class="field" name="sku" value="{{ old('sku', $item->sku) }}" required></label>
            <label>Name<input class="field" name="name" value="{{ old('name', $item->name) }}" required></label>
            <label>Category<input class="field" name="category" value="{{ old('category', $item->category) }}"></label>
            <label>Unit<input class="field" name="unit" value="{{ old('unit', $item->unit) }}" required></label>
            <label>Qty on hand<input class="field" type="number" step="0.001" name="quantity_on_hand" value="{{ old('quantity_on_hand', $item->quantity_on_hand) }}" required></label>
            <label>Reorder level<input class="field" type="number" step="0.001" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}" required></label>
            <label>Unit cost (৳)<input class="field" type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" required></label>
            <label>Location<input class="field" name="location" value="{{ old('location', $item->location) }}"></label>
            <label>Supplier
                <select class="field" name="supplier_id">
                    <option value="">— None —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $item->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Status
                <select class="field" name="status">
                    <option value="active" @selected(old('status', $item->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status', $item->status)==='inactive')>Inactive</option>
                </select>
            </label>
        </div>

        <div class="form-actions">
            <button class="btn btn-gold" type="submit">{{ $mode === 'create' ? 'Create Item' : 'Save Changes' }}</button>
        </div>
    </form>
</section>
@endsection
