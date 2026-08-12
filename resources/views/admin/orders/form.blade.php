@extends('admin.layouts.app')

@section('title', 'Edit Order')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $order->order_number }}</h1>
        <p>Update status, payment, and assignment.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.orders.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ route('admin.orders.update', $order) }}">
        @csrf @method('PUT')
        <div class="form-grid">
            <label>Status
                <select class="field" name="status">
                    @foreach (['pending','preparing','ready','on_the_way','completed','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $order->status)===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Payment
                <select class="field" name="payment_status">
                    @foreach (['unpaid','paid','refunded'] as $s)
                        <option value="{{ $s }}" @selected(old('payment_status', $order->payment_status)===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Table
                <select class="field" name="table_id">
                    <option value="">— None —</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}" @selected((string)old('table_id', $order->table_id)===(string)$table->id)>{{ $table->code }}</option>
                    @endforeach
                </select>
            </label>
            <label>Customer
                <select class="field" name="customer_id">
                    <option value="">— Guest —</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string)old('customer_id', $order->customer_id)===(string)$customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Guest name<input class="field" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}"></label>
            <label>Meta<input class="field" name="meta" value="{{ old('meta', $order->meta) }}"></label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $order->notes) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:16px 0 10px">Line items</h3>
        <ul class="list">
            @foreach ($order->items as $item)
                <li>
                    <div><strong>{{ $item->item_name }}</strong><small>x{{ $item->quantity }}</small></div>
                    <span class="sell-rev">৳ {{ number_format((float)$item->line_total, 2) }}</span>
                </li>
            @endforeach
        </ul>
        <p class="chart-meta" style="margin-top:12px"><b>Total ৳ {{ number_format((float)$order->total, 2) }}</b></p>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Order</button></div>
    </form>
</section>
@endsection
