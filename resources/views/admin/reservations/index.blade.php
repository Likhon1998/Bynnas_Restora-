@extends('admin.layouts.app')

@section('title', 'Reservations')

@section('content')
<div class="page-head">
    <div>
        <h1>Reservations</h1>
        <p>Manage upcoming bookings and seating plans.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.reservations.create') }}" class="btn btn-gold">+ New Reservation</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="card kpi blue"><p class="kpi-label">Today</p><p class="kpi-value">{{ $stats['today'] }}</p></article>
    <article class="card kpi orange"><p class="kpi-label">Pending</p><p class="kpi-value">{{ $stats['pending'] }}</p></article>
    <article class="card kpi green"><p class="kpi-label">Confirmed</p><p class="kpi-value">{{ $stats['confirmed'] }}</p></article>
</div>

<section class="card" style="margin-top:12px">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search guest / phone...">
        <select class="field" name="status">
            <option value="">All statuses</option>
            @foreach (['pending','confirmed','seated','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected($filters['status']===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>When</th>
                    <th>Guests</th>
                    <th>Table</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->guest_name }}</strong>
                            <small class="muted">{{ $r->phone ?: '—' }}</small>
                        </td>
                        <td>{{ $r->reserved_at?->format('M d, Y g:i A') }}</td>
                        <td>{{ $r->guests }}</td>
                        <td>{{ $r->table?->code ?? '—' }}</td>
                        <td><span class="pill {{ $r->statusTone() }}">{{ ucfirst($r->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.reservations.edit', $r) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.reservations.destroy', $r) }}" onsubmit="return confirm('Delete reservation?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $reservations->links('pagination.admin') }}</div>
</section>
@endsection
