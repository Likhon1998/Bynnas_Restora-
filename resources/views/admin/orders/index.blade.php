@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="page-head">
    <div>
        <h1>Orders</h1>
        <p>Live kitchen and delivery queue with status control.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.pos.index') }}" target="_blank" rel="noopener" class="btn btn-gold">+ New POS Order</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <article class="card kpi purple"><p class="kpi-label">Open Orders</p><p class="kpi-value">{{ $stats['open'] }}</p></article>
    <article class="card kpi orange"><p class="kpi-label">Preparing</p><p class="kpi-value">{{ $stats['preparing'] }}</p></article>
    <article class="card kpi green"><p class="kpi-label">Ready</p><p class="kpi-value">{{ $stats['ready'] }}</p></article>
    <article class="card kpi gold"><p class="kpi-label">Today Paid</p><p class="kpi-value">৳ {{ number_format((float)$stats['today_sales'], 2) }}</p></article>
</div>

<section class="card" style="margin-top:12px">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search order # / guest...">
        <select class="field" name="type">
            <option value="">All types</option>
            @foreach (['dinein','takeaway','delivery','qr','walkin'] as $t)
                <option value="{{ $t }}" @selected($filters['type']===$t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select class="field" name="status">
            <option value="">All statuses</option>
            @foreach (['pending','preparing','ready','on_the_way','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected($filters['status']===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Type</th>
                    <th>Guest / Table</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                            <small class="muted">{{ optional($order->placed_at)->diffForHumans() }}</small>
                        </td>
                        <td>{{ $order->typeLabel() }}</td>
                        <td>
                            {{ $order->customer_name ?: ($order->customer?->name ?? 'Guest') }}
                            <small class="muted">{{ $order->table?->code ? 'Table '.$order->table->code : ($order->meta ?: '—') }}</small>
                        </td>
                        <td>{{ $order->items->sum('quantity') }}</td>
                        <td>৳ {{ number_format((float)$order->total, 2) }}</td>
                        <td><span class="pill {{ $order->statusTone() }}">{{ $order->statusLabel() }}</span></td>
                        <td><span class="pill {{ $order->payment_status === 'paid' ? 'green' : 'slate' }}">{{ ucfirst($order->payment_status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.orders.edit', $order) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete order?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No orders yet. Create one from POS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $orders->links('pagination.admin') }}</div>
</section>
@endsection
