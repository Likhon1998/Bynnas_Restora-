@extends('admin.layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="page-head">
    <div>
        <h1>Suppliers</h1>
        <p>Manage vendor contacts and purchase relationships.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-gold">+ Add Supplier</a>
    </div>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search suppliers...">
        <button class="btn btn-solid" type="submit">Search</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>POs</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td><strong>{{ $supplier->name }}</strong><small class="muted">{{ $supplier->address }}</small></td>
                        <td>{{ $supplier->contact_person ?? '—' }}</td>
                        <td>{{ $supplier->phone ?? '—' }}</td>
                        <td>{{ $supplier->email ?? '—' }}</td>
                        <td>{{ $supplier->purchase_orders_count }}</td>
                        <td><span class="pill {{ $supplier->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($supplier->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete supplier?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $suppliers->links('pagination.admin') }}</div>
</section>
@endsection
