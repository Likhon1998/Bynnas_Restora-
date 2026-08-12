@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Customer' : 'Edit Customer')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Customer' : 'Edit Customer' }}</h1>
        <p>Profile and loyalty membership details.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.customers.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.customers.store') : route('admin.customers.update', $customer) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Name<input class="field" name="name" value="{{ old('name', $customer->name) }}" required></label>
            <label>Email<input class="field" type="email" name="email" value="{{ old('email', $customer->email) }}"></label>
            <label>Phone<input class="field" name="phone" value="{{ old('phone', $customer->phone) }}"></label>
            <label>Membership
                <select class="field" name="membership_tier">
                    @foreach (['standard','silver','gold','platinum'] as $tier)
                        <option value="{{ $tier }}" @selected(old('membership_tier', $customer->membership_tier)===$tier)>{{ ucfirst($tier) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Loyalty points<input class="field" type="number" min="0" name="loyalty_points" value="{{ old('loyalty_points', $customer->loyalty_points ?? 0) }}"></label>
            <label>Lifetime spend<input class="field" type="number" step="0.01" name="lifetime_spend" value="{{ old('lifetime_spend', $customer->lifetime_spend ?? 0) }}"></label>
            <label>Joined on<input class="field" type="date" name="joined_on" value="{{ old('joined_on', optional($customer->joined_on)->format('Y-m-d') ?? $customer->joined_on) }}"></label>
            <label>Status
                <select class="field" name="status">
                    <option value="active" @selected(old('status', $customer->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status', $customer->status)==='inactive')>Inactive</option>
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $customer->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Customer</button></div>
    </form>
</section>
@endsection
