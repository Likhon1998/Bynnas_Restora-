@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Create Purchase Order' : 'Edit Purchase Order')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Create Purchase Order' : 'Edit Purchase Order' }}</h1>
        <p>Set status to <strong>received</strong> to add quantities into inventory.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.purchase-orders.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.purchase-orders.store') : route('admin.purchase-orders.update', $order) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>PO number<input class="field" name="po_number" value="{{ old('po_number', $order->po_number) }}" required></label>
            <label>Supplier
                <select class="field" name="supplier_id" required>
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $order->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Order date<input class="field" type="date" name="order_date" value="{{ old('order_date', optional($order->order_date)->format('Y-m-d') ?? $order->order_date) }}" required></label>
            <label>Expected date<input class="field" type="date" name="expected_date" value="{{ old('expected_date', optional($order->expected_date)->format('Y-m-d')) }}"></label>
            <label>Status
                <select class="field" name="status">
                    @foreach (['draft','sent','partial','received','cancelled'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $order->status)===$st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $order->notes) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:18px 0 10px">Line items</h3>
        <div id="poLines" class="line-rows">
            @foreach (old('lines', $lineRows) as $i => $row)
                <div class="line-row">
                    <select class="field" name="lines[{{ $i }}][inventory_item_id]" required>
                        <option value="">Select item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected((string)($row['inventory_item_id'] ?? '') === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input class="field" type="number" step="0.001" name="lines[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? '' }}" placeholder="Qty" required>
                    <input class="field" type="number" step="0.01" name="lines[{{ $i }}][unit_cost]" value="{{ $row['unit_cost'] ?? '' }}" placeholder="Unit cost" required>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn" id="addPoLine" style="margin-top:8px">+ Add line</button>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Purchase Order</button></div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('poLines');
    var btn = document.getElementById('addPoLine');
    if (!wrap || !btn) return;
    var options = `@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach`;
    btn.addEventListener('click', function () {
        var i = wrap.children.length;
        var div = document.createElement('div');
        div.className = 'line-row';
        div.innerHTML = '<select class="field" name="lines['+i+'][inventory_item_id]" required><option value="">Select item</option>'+options+'</select>'
            + '<input class="field" type="number" step="0.001" name="lines['+i+'][quantity]" placeholder="Qty" required>'
            + '<input class="field" type="number" step="0.01" name="lines['+i+'][unit_cost]" placeholder="Unit cost" required>';
        wrap.appendChild(div);
    });
})();
</script>
@endpush
