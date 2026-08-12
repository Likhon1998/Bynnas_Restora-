@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="page-head">
    <div>
        <h1>Inventory</h1>
        <p>Live stock levels across kitchen and storage locations.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.inventory.create') }}" class="btn btn-gold">+ Add Item</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="card kpi gold"><p class="kpi-label">Total Items</p><p class="kpi-value">{{ $stats['total'] }}</p></article>
    <article class="card kpi orange"><p class="kpi-label">Low Stock Alerts</p><p class="kpi-value">{{ $stats['low'] }}</p></article>
    <article class="card kpi green"><p class="kpi-label">Stock Value</p><p class="kpi-value">৳ {{ number_format((float) $stats['value'], 2) }}</p></article>
</div>

<section class="card" style="margin-top:12px">
    <form class="toolbar" method="GET" action="{{ route('admin.inventory.index') }}">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, SKU, category...">
        <select class="field" name="status">
            <option value="">All status</option>
            <option value="low" @selected($filters['status']==='low')>Low stock</option>
            <option value="active" @selected($filters['status']==='active')>Active</option>
            <option value="inactive" @selected($filters['status']==='inactive')>Inactive</option>
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Item</th>
                    <th>On hand</th>
                    <th>Reorder</th>
                    <th>Cost</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->sku }}</td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            <small class="muted">{{ $item->category }} · {{ $item->location }}</small>
                        </td>
                        <td class="{{ $item->isLowStock() ? 'text-warn' : '' }}">{{ rtrim(rtrim(number_format((float)$item->quantity_on_hand, 3, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                        <td>{{ rtrim(rtrim(number_format((float)$item->reorder_level, 3, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                        <td>৳ {{ number_format((float)$item->unit_cost, 2) }}</td>
                        <td>{{ $item->supplier?->name ?? '—' }}</td>
                        <td>
                            @if ($item->isLowStock())
                                <span class="pill red">Low</span>
                            @else
                                <span class="pill {{ $item->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.inventory.edit', $item) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.inventory.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No inventory items yet. Add your first item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $items->links('pagination.admin') }}</div>
</section>
@endsection
