@extends('admin.layouts.app')

@section('title', 'CRM & Loyalty')

@section('content')
<div class="page-head">
    <div>
        <h1>CRM & Loyalty</h1>
        <p>Membership performance and point ledger.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.customers.index') }}" class="btn">Customers</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <article class="card kpi gold"><p class="kpi-label">Total Customers</p><p class="kpi-value">{{ $stats['total_customers'] }}</p></article>
    <article class="card kpi purple"><p class="kpi-label">Members</p><p class="kpi-value">{{ $stats['members'] }}</p></article>
    <article class="card kpi blue"><p class="kpi-label">Points Pool</p><p class="kpi-value">{{ number_format($stats['points_pool']) }}</p></article>
    <article class="card kpi green"><p class="kpi-label">Points Redeemed</p><p class="kpi-value">{{ number_format($stats['redeemed']) }}</p></article>
</div>

<div class="ops-grid" style="margin-top:12px">
    <section class="card">
        <div class="card-head"><h3 class="card-title">Adjust Points</h3></div>
        <form method="POST" action="{{ route('admin.crm.transactions.store') }}">
            @csrf
            <div class="form-grid">
                <label class="span-2">Customer
                    <select class="field" name="customer_id" required>
                        <option value="">Select customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->loyalty_points }} pts)</option>
                        @endforeach
                    </select>
                </label>
                <label>Type
                    <select class="field" name="type">
                        <option value="earn">Earn</option>
                        <option value="redeem">Redeem</option>
                        <option value="adjust">Adjust (+)</option>
                    </select>
                </label>
                <label>Points<input class="field" type="number" min="1" name="points" required></label>
                <label class="span-2">Description<input class="field" name="description" placeholder="Birthday bonus, redemption..."></label>
            </div>
            <div class="form-actions"><button class="btn btn-gold" type="submit">Save Transaction</button></div>
        </form>
    </section>

    <section class="card">
        <div class="card-head"><h3 class="card-title">Top Members</h3></div>
        <ul class="list">
            @forelse ($topMembers as $member)
                <li>
                    <div>
                        <strong>{{ $member->name }}</strong>
                        <small>{{ ucfirst($member->membership_tier) }}</small>
                    </div>
                    <span class="sell-rev">{{ $member->loyalty_points }} pts</span>
                </li>
            @empty
                <li><small>No members yet.</small></li>
            @endforelse
        </ul>
    </section>
</div>

<section class="card" style="margin-top:12px">
    <div class="card-head"><h3 class="card-title">Recent Loyalty Activity</h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Points</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $tx)
                    <tr>
                        <td>{{ $tx->created_at?->diffForHumans() }}</td>
                        <td>{{ $tx->customer?->name }}</td>
                        <td><span class="pill {{ $tx->type === 'redeem' ? 'amber' : 'green' }}">{{ ucfirst($tx->type) }}</span></td>
                        <td>{{ $tx->points }}</td>
                        <td>{{ $tx->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No loyalty transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
