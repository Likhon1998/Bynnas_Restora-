@extends('admin.layouts.app')

@section('title', 'Accounting')

@section('content')
<div class="page-head">
    <div>
        <h1>Accounting</h1>
        <p>Live P&amp;L from POS sales, purchases, wastage, and recorded expenses.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-gold">+ Add Expense</a>
        <a href="{{ route('admin.reports.index') }}" class="btn">View Reports</a>
    </div>
</div>

<section class="card" style="margin-bottom:16px">
    <form class="toolbar" method="GET">
        <label>From <input class="field" type="date" name="from" value="{{ $filters['from'] }}"></label>
        <label>To <input class="field" type="date" name="to" value="{{ $filters['to'] }}"></label>
        <button class="btn btn-solid" type="submit">Apply</button>
    </form>
</section>

<section class="kpi-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
    @foreach ($summary as $card)
        <div class="card" style="padding:16px;{{ !empty($card['highlight']) ? 'border-color:#c47a25;background:#fffaf3' : '' }}">
            <small class="muted" style="display:block;margin-bottom:6px">{{ $card['label'] }}</small>
            <strong style="font-size:1.25rem">৳ {{ number_format($card['value'], 2) }}</strong>
            @if (!empty($card['meta']))
                <div class="muted" style="margin-top:6px;font-size:12px">{{ $card['meta'] }}</div>
            @endif
        </div>
    @endforeach
</section>

@if ($discounts > 0)
    <p class="muted" style="margin-bottom:12px">Discounts given in this period: <strong>৳ {{ number_format($discounts, 2) }}</strong></p>
@endif

<section class="card">
    <div class="page-head" style="margin-bottom:12px;padding:0">
        <div>
            <h2 style="font-size:1.05rem;margin:0">Ledger feed</h2>
            <p style="margin:4px 0 0">Recent paid orders and expenses connected to the website POS.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>In</th>
                    <th>Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ledger as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td><span class="pill {{ $row['tone'] }}">{{ $row['type'] }}</span></td>
                        <td>{{ $row['reference'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td>{{ $row['in'] > 0 ? '৳ '.number_format($row['in'], 2) : '—' }}</td>
                        <td>{{ $row['out'] > 0 ? '৳ '.number_format($row['out'], 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No ledger activity in this date range. Complete a POS payment or add an expense.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
