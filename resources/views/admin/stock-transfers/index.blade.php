@extends('admin.layouts.app')

@section('title', 'Stock Transfers')

@section('content')
<div class="page-head">
    <div>
        <h1>Stock Transfers</h1>
        <p>Move inventory between kitchen, storage, and outlets.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-gold">+ New Transfer</a>
    </div>
</div>

<section class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transfer #</th>
                    <th>Item</th>
                    <th>From → To</th>
                    <th>Qty</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transfers as $transfer)
                    <tr>
                        <td><strong>{{ $transfer->transfer_number }}</strong></td>
                        <td>{{ $transfer->inventoryItem?->name }}</td>
                        <td>{{ $transfer->from_location }} → {{ $transfer->to_location }}</td>
                        <td>{{ rtrim(rtrim(number_format((float)$transfer->quantity, 3, '.', ''), '0'), '.') }}</td>
                        <td>{{ $transfer->transfer_date?->format('M d, Y') }}</td>
                        <td><span class="pill {{ $transfer->status === 'completed' ? 'green' : ($transfer->status === 'pending' ? 'amber' : 'slate') }}">{{ ucfirst($transfer->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.stock-transfers.edit', $transfer) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.stock-transfers.destroy', $transfer) }}" onsubmit="return confirm('Delete transfer?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No stock transfers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $transfers->links('pagination.admin') }}</div>
</section>
@endsection
