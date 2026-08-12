@extends('admin.layouts.app')

@section('title', 'Wastage & Variance')

@section('content')
<div class="page-head">
    <div>
        <h1>Wastage & Variance</h1>
        <p>Track spoilage and stock count differences with cost impact.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.wastage.create') }}" class="btn btn-gold">+ Record Entry</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="card kpi orange"><p class="kpi-label">Total Cost Impact</p><p class="kpi-value">৳ {{ number_format((float)$stats['total_cost'], 2) }}</p></article>
    <article class="card kpi red"><p class="kpi-label">Wastage Entries</p><p class="kpi-value">{{ $stats['wastage'] }}</p></article>
    <article class="card kpi blue"><p class="kpi-label">Variance Entries</p><p class="kpi-value">{{ $stats['variance'] }}</p></article>
</div>

<section class="card" style="margin-top:12px">
    <form class="toolbar" method="GET">
        <select class="field" name="type">
            <option value="">All types</option>
            <option value="wastage" @selected($filters['type']==='wastage')>Wastage</option>
            <option value="variance" @selected($filters['type']==='variance')>Variance</option>
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Cost</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->recorded_on?->format('M d, Y') }}</td>
                        <td>{{ $record->inventoryItem?->name }}</td>
                        <td><span class="pill {{ $record->type === 'wastage' ? 'red' : 'blue' }}">{{ ucfirst($record->type) }}</span></td>
                        <td>{{ rtrim(rtrim(number_format((float)$record->quantity, 3, '.', ''), '0'), '.') }}</td>
                        <td>{{ $record->reason }}</td>
                        <td>৳ {{ number_format((float)$record->cost_impact, 2) }}</td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.wastage.edit', $record) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.wastage.destroy', $record) }}" onsubmit="return confirm('Delete record?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No wastage or variance records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $records->links('pagination.admin') }}</div>
</section>
@endsection
