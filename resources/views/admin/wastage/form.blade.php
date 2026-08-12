@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Record Wastage / Variance' : 'Edit Record')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Record Wastage / Variance' : 'Edit Record' }}</h1>
        <p>Wastage entries automatically reduce on-hand stock.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.wastage.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.wastage.store') : route('admin.wastage.update', $record) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Item
                <select class="field" name="inventory_item_id" required>
                    <option value="">Select item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" @selected((string) old('inventory_item_id', $record->inventory_item_id) === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Type
                <select class="field" name="type">
                    <option value="wastage" @selected(old('type', $record->type)==='wastage')>Wastage</option>
                    <option value="variance" @selected(old('type', $record->type)==='variance')>Variance</option>
                </select>
            </label>
            <label>Quantity<input class="field" type="number" step="0.001" name="quantity" value="{{ old('quantity', $record->quantity) }}" required></label>
            <label>Date<input class="field" type="date" name="recorded_on" value="{{ old('recorded_on', optional($record->recorded_on)->format('Y-m-d') ?? $record->recorded_on) }}" required></label>
            <label class="span-2">Reason<input class="field" name="reason" value="{{ old('reason', $record->reason) }}" required></label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $record->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Record</button></div>
    </form>
</section>
@endsection
