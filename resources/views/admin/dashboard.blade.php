@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php $first = explode(' ', $user->name)[0] ?? 'Admin'; @endphp
<div class="page-head">
                <div>
                    <h1>Good morning, {{ $first }}! 👋</h1>
                    <p>Here’s what’s happening in your restaurant today.</p>
                </div>
                <div class="page-head-actions">
                    <button type="button" class="btn">May 20, 2026</button>
                    <button type="button" class="btn btn-gold">⏻ End of Day</button>
                </div>
            </div>

            {{-- KPIs: no sparklines/sliders --}}
            <div class="kpi-grid">
                @foreach ($kpis as $kpi)
                    <article class="card kpi {{ $kpi['tone'] }}">
                        <div class="kpi-top">
                            <div>
                                <p class="kpi-label">{{ $kpi['title'] }}</p>
                                <p class="kpi-value">{{ $kpi['value'] }}</p>
                                <p class="kpi-trend">▲ {{ $kpi['change'] }} <span>vs yesterday</span></p>
                            </div>
                            <div class="kpi-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="row-ops">
                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Table / Floor Overview</h3></div>
                    <ul class="legend legend-chips">
                        @foreach ($tableLegend as $leg)
                            <li>
                                <i class="dot" style="background:{{ $leg['color'] }}"></i>
                                <span>{{ $leg['label'] }}</span>
                                <strong>{{ $leg['count'] }}</strong>
                            </li>
                        @endforeach
                    </ul>
                    <div class="floor-map">
                        @foreach ($floorTables as $t)
                            <div class="table-seat {{ $t['status'] }}">{{ $t['id'] }}</div>
                        @endforeach
                    </div>
                    <div class="floor-stats">
                        <div class="stat-box"><small>Seated</small><strong>12 Tables</strong></div>
                        <div class="stat-box"><small>Occupied</small><strong>32 / 48</strong></div>
                        <div class="stat-box"><small>Waiting</small><strong>2 Orders</strong></div>
                        <div class="stat-box"><small>Turnover</small><strong>2.3x</strong></div>
                    </div>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Live Order Queue</h3></div>
                    <div class="tabs">
                        @foreach ($orderTabs as $i => $tab)
                            <button type="button" class="tab {{ $i === 0 ? 'active' : '' }}">{{ $tab['label'] }} ({{ $tab['count'] }})</button>
                        @endforeach
                    </div>
                    <ul class="list scroll-pane">
                        @foreach ($liveOrders as $order)
                            <li>
                                <div>
                                    <strong>{{ $order['id'] }}</strong>
                                    <small>{{ $order['meta'] }} · {{ $order['ago'] }}</small>
                                </div>
                                <span class="pill {{ $order['tone'] }}">{{ $order['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Revenue Overview</h3></div>
                    <p class="chart-meta"><b>৳ 185,420.50</b> <span class="up">+14.6%</span> vs last week</p>
                    <div class="chart-wrap">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="row-4">
                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Inventory Alert</h3></div>
                    <ul class="list scroll-pane">
                        @foreach ($inventoryAlerts as $item)
                            <li class="list-stack">
                                <div class="inv-row">
                                    <div class="inv-meta">
                                        <strong>{{ $item['name'] }}</strong>
                                        <span>{{ $item['left'] }} / min {{ $item['min'] }}</span>
                                    </div>
                                    <div class="bar {{ $item['pct'] > 30 ? 'warn' : '' }}"><i style="width:{{ $item['pct'] }}%"></i></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-solid card-cta">Create Purchase Order</a>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Purchase Orders</h3></div>
                    <ul class="list scroll-pane">
                        @foreach ($purchaseOrders as $po)
                            <li>
                                <div>
                                    <strong>{{ $po['id'] }}</strong>
                                    <small>{{ $po['supplier'] }} · {{ $po['date'] }}</small>
                                </div>
                                <span class="pill {{ $po['tone'] }}">{{ $po['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Financial Snapshot</h3></div>
                    <ul class="finance">
                        @foreach ($financialSnapshot as $row)
                            <li class="{{ !empty($row['highlight']) ? 'is-highlight' : '' }}">
                                <span>{{ $row['label'] }}</span>
                                <div>
                                    <strong>{{ $row['value'] }}</strong>
                                    @isset($row['meta'])<em>{{ $row['meta'] }}</em>@endisset
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Top Selling Items</h3></div>
                    <ul class="list scroll-pane">
                        @foreach ($topSelling as $item)
                            <li>
                                <div class="sell">
                                    <span class="sell-rank">{{ $item['rank'] }}</span>
                                    <img src="{{ $item['image'] }}" alt="">
                                    <div>
                                        <strong>{{ $item['name'] }}</strong>
                                        <small>{{ $item['sold'] }} sold</small>
                                    </div>
                                </div>
                                <span class="sell-rev">{{ $item['revenue'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <div class="row-4">
                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Upcoming Reservations</h3></div>
                    <ul class="list scroll-pane">
                        @foreach ($upcomingReservations as $r)
                            <li>
                                <div>
                                    <strong>{{ $r['name'] }}</strong>
                                    <small>{{ $r['time'] }} · {{ $r['guests'] }} guests</small>
                                </div>
                                <span class="pill {{ $r['tone'] }}">{{ $r['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Today’s Staff Performance</h3></div>
                    <div class="rings">
                        @foreach ($staffPerformance as $s)
                            @php $r=22; $c=2*3.1416*$r; $off=$c-($s['pct']/100)*$c; @endphp
                            <div class="ring">
                                <svg width="64" height="64" viewBox="0 0 64 64">
                                    <circle cx="32" cy="32" r="{{ $r }}" stroke="rgba(15,23,42,0.1)" stroke-width="5" fill="none"/>
                                    <circle cx="32" cy="32" r="{{ $r }}" stroke="#c47a25" stroke-width="5" fill="none" stroke-linecap="round" stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $off }}" transform="rotate(-90 32 32)"/>
                                    <text x="32" y="36" text-anchor="middle" fill="#0f172a" font-size="11" font-weight="700">{{ $s['pct'] }}%</text>
                                </svg>
                                <p>{{ $s['label'] }}</p>
                                <strong>{{ $s['value'] }}</strong>
                                @if($s['change'])<em>{{ $s['change'] }}</em>@endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">CRM & Loyalty Overview</h3></div>
                    <div class="crm">
                        @foreach ($crmStats as $stat)
                            <div class="crm-item">
                                <div>
                                    <small>{{ $stat['label'] }}</small>
                                    <strong>{{ $stat['value'] }}</strong>
                                </div>
                                <span class="up">{{ $stat['change'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.crm.index') }}" class="btn btn-gold card-cta">Go to CRM Dashboard →</a>
                </section>

                <section class="card card-panel">
                    <div class="card-head"><h3 class="card-title">Recent Activities</h3></div>
                    <ul class="activity scroll-pane">
                        @foreach ($recentActivities as $a)
                            @php
                                $color = match($a['tone']) {
                                    'green' => '#059669',
                                    'red' => '#dc2626',
                                    'blue' => '#2563eb',
                                    'purple' => '#7c3aed',
                                    default => '#ea580c',
                                };
                            @endphp
                            <li>
                                <span class="pulse" style="background:{{ $color }}"></span>
                                <div>
                                    <p>{{ $a['text'] }}</p>
                                    <small>{{ $a['time'] }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var ctx = document.getElementById('revenueChart');
    if (!ctx || !window.Chart) return;
    var labels = @json(array_column($revenueWeek, 'day'));
    var values = @json(array_column($revenueWeek, 'value'));
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                borderColor: '#c47a25',
                backgroundColor: 'rgba(196,122,37,0.12)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#c47a25',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(15,23,42,0.06)' }, ticks: { color: '#64748b' } },
                y: {
                    grid: { color: 'rgba(15,23,42,0.08)' },
                    ticks: { color: '#64748b', callback: function (v) { return (v / 1000) + 'k'; } }
                }
            }
        }
    });
})();
</script>
@endpush
