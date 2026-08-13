@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Table' : 'Edit Table')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Table' : 'Edit Table' }}</h1>
        <p>Update seating capacity and live floor status.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.tables.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.tables.store') : route('admin.tables.update', $table) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Code<input class="field" name="code" value="{{ old('code', $table->code) }}" required></label>
            <label>Capacity<input class="field" type="number" min="1" name="capacity" value="{{ old('capacity', $table->capacity) }}" required></label>
            <label>Zone<input class="field" name="zone" value="{{ old('zone', $table->zone) }}" required></label>
            <label>Status
                <select class="field" name="status">
                    @foreach (['available','seated','ordered','preparing','ready','waiting'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $table->status)===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="form-actions">
            <button class="btn btn-gold" type="submit">Save Table</button>
            @if ($mode === 'edit' && $table->id)
                <a class="btn" href="{{ route('admin.tables.qr', $table) }}">View QR</a>
            @endif
        </div>
    </form>
</section>
@endsection
