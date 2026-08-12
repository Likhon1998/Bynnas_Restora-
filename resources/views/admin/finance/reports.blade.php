@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-head">
    <div>
        <h1>Reports</h1>
        <p>Sales, tax, and expense breakdowns from live website &amp; POS data.</p>
    </div>
</div>

<section class="card" style="margin-bottom:16px">
    <form class="toolbar" method="GET">
        <label>From <input class="field" type="date" name="from" value="{{ $filters['from'] }}"></label>
        <label>To <input class="field" type="date" name="to" value="{{ $filters['to'] }}"></label>
        <button class="btn btn-solid" type="submit">Apply</button>
    </form>
</section>

<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
    @foreach ($kpis as $kpi)
        <div class="card" style="padding:16px">
            <small class="muted" style="display:block;margin-bottom:6px">{{ $kpi['label'] }}</small>
            <strong style="font-size:1.2rem">
                @if (!empty($kpi['plain']))
                    {{ number_format($kpi['value']) }}
                @else
                    ৳ {{ number_format($kpi['value'], 2) }}
                @endif
            </strong>
        </div>
    @endforeach
</section>

<div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:16px">
    <section class="card">
        <h2 style="font-size:1.05rem;margin:0 0 12px">Daily paid sales</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Sales</th>
                        <th>Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesByDay as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('M d, Y') }}</td>
                            <td>{{ $row->orders_count }}</td>
                            <td>৳ {{ number_format((float) $row->sales, 2) }}</td>
                            <td>৳ {{ number_format((float) $row->tax, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No paid sales in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.05rem;margin:0 0 12px">Sales by order type</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesByType as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['orders_count'] }}</td>
                            <td>৳ {{ number_format($row['sales'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
    <section class="card">
        <h2 style="font-size:1.05rem;margin:0 0 12px">Top selling items</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topItems as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ (int) $item->qty }}</td>
                            <td>৳ {{ number_format((float) $item->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">No item sales yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.05rem;margin:0 0 12px">Expenses by category</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenseByCategory as $row)
                        <tr>
                            <td>{{ $row['category'] }}</td>
                            <td>৳ {{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
