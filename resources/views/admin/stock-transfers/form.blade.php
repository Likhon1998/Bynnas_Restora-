@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'New Stock Transfer' : 'Edit Stock Transfer')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'New Stock Transfer' : 'Edit Stock Transfer' }}</h1>
        <p>Record movement of stock between locations.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.stock-transfers.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.stock-transfers.store') : route('admin.stock-transfers.update', $transfer) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Transfer #<input class="field" name="transfer_number" value="{{ old('transfer_number', $transfer->transfer_number) }}" required></label>
            <label>Item
                <select class="field" name="inventory_item_id" required>
                    <option value="">Select item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" @selected((string) old('inventory_item_id', $transfer->inventory_item_id) === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>From<input class="field" name="from_location" value="{{ old('from_location', $transfer->from_location) }}" required></label>
            <label>To<input class="field" name="to_location" value="{{ old('to_location', $transfer->to_location) }}" required></label>
            <label>Quantity<input class="field" type="number" step="0.001" name="quantity" value="{{ old('quantity', $transfer->quantity) }}" required></label>
            <label>Date<input class="field" type="date" name="transfer_date" value="{{ old('transfer_date', optional($transfer->transfer_date)->format('Y-m-d') ?? $transfer->transfer_date) }}" required></label>
            <label>Status
                <select class="field" name="status">
                    @foreach (['pending','completed','cancelled'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $transfer->status)===$st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $transfer->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Transfer</button></div>
    </form>
</section>
@endsection
