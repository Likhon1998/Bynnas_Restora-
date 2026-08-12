@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Supplier' : 'Edit Supplier')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Supplier' : 'Edit Supplier' }}</h1>
        <p>Vendor details used for purchase orders.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.suppliers.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.suppliers.store') : route('admin.suppliers.update', $supplier) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Name<input class="field" name="name" value="{{ old('name', $supplier->name) }}" required></label>
            <label>Contact person<input class="field" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"></label>
            <label>Email<input class="field" type="email" name="email" value="{{ old('email', $supplier->email) }}"></label>
            <label>Phone<input class="field" name="phone" value="{{ old('phone', $supplier->phone) }}"></label>
            <label class="span-2">Address<input class="field" name="address" value="{{ old('address', $supplier->address) }}"></label>
            <label>Status
                <select class="field" name="status">
                    <option value="active" @selected(old('status', $supplier->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status', $supplier->status)==='inactive')>Inactive</option>
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="3">{{ old('notes', $supplier->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Supplier</button></div>
    </form>
</section>
@endsection
