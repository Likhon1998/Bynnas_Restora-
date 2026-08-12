@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="page-head">
    <div>
        <h1>Customers</h1>
        <p>Guest profiles linked to orders, reservations, and loyalty.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.customers.create') }}" class="btn btn-gold">+ Add Customer</a>
    </div>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, email, phone...">
        <button class="btn btn-solid" type="submit">Search</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Tier</th>
                    <th>Points</th>
                    <th>Spend</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <strong>{{ $customer->name }}</strong>
                            <small class="muted">{{ $customer->phone ?: $customer->email ?: '—' }}</small>
                        </td>
                        <td>{{ ucfirst($customer->membership_tier) }}</td>
                        <td>{{ $customer->loyalty_points }}</td>
                        <td>৳ {{ number_format((float)$customer->lifetime_spend, 2) }}</td>
                        <td>{{ $customer->orders_count }}</td>
                        <td><span class="pill {{ $customer->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($customer->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete customer?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $customers->links('pagination.admin') }}</div>
</section>
@endsection
