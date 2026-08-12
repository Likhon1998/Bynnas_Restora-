@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'New Reservation' : 'Edit Reservation')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'New Reservation' : 'Edit Reservation' }}</h1>
        <p>Book a table for guests.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.reservations.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.reservations.store') : route('admin.reservations.update', $reservation) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Guest name<input class="field" name="guest_name" value="{{ old('guest_name', $reservation->guest_name) }}" required></label>
            <label>Phone<input class="field" name="phone" value="{{ old('phone', $reservation->phone) }}"></label>
            <label>Guests<input class="field" type="number" min="1" name="guests" value="{{ old('guests', $reservation->guests) }}" required></label>
            <label>Date & time
                <input class="field" type="datetime-local" name="reserved_at" value="{{ old('reserved_at', optional($reservation->reserved_at)->format('Y-m-d\\TH:i') ?? $reservation->reserved_at) }}" required>
            </label>
            <label>Customer account
                <select class="field" name="customer_id">
                    <option value="">— Optional —</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string)old('customer_id', $reservation->customer_id)===(string)$customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Table
                <select class="field" name="table_id">
                    <option value="">— Auto / later —</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}" @selected((string)old('table_id', $reservation->table_id)===(string)$table->id)>{{ $table->code }} ({{ $table->capacity }} seats)</option>
                    @endforeach
                </select>
            </label>
            <label>Status
                <select class="field" name="status">
                    @foreach (['pending','confirmed','seated','completed','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $reservation->status)===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="2">{{ old('notes', $reservation->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Reservation</button></div>
    </form>
</section>
@endsection
