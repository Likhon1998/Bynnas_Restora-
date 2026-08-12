@extends('admin.layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="page-head">
    <div>
        <h1>Purchase Orders</h1>
        <p>Create, track, and receive supplier orders into stock.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-gold">+ Create PO</a>
    </div>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <select class="field" name="status">
            <option value="">All statuses</option>
            @foreach (['draft','sent','partial','received','cancelled'] as $st)
                <option value="{{ $st }}" @selected($filters['status']===$st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><strong>{{ $order->po_number }}</strong></td>
                        <td>{{ $order->supplier?->name }}</td>
                        <td>{{ $order->order_date?->format('M d, Y') }}</td>
                        <td>{{ $order->items_count }}</td>
                        <td>৳ {{ number_format((float)$order->total_amount, 2) }}</td>
                        <td><span class="pill {{ $order->statusTone() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.purchase-orders.edit', $order) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.purchase-orders.destroy', $order) }}" onsubmit="return confirm('Delete PO?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No purchase orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $orders->links('pagination.admin') }}</div>
</section>
@endsection
