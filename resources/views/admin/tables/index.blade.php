@extends('admin.layouts.app')

@section('title', 'Table Management')

@section('content')
<div class="page-head">
    <div>
        <h1>Table Management</h1>
        <p>Live floor status across dining zones.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.tables.create') }}" class="btn btn-gold">+ Add Table</a>
    </div>
</div>

<section class="card">
    <ul class="legend legend-chips">
        @foreach ($legend as $leg)
            <li>
                <i class="dot table-seat {{ $leg['status'] }}" style="width:10px;height:10px;display:inline-block"></i>
                <span>{{ $leg['label'] }}</span>
                <strong>{{ $leg['count'] }}</strong>
            </li>
        @endforeach
    </ul>

    <div class="floor-map" style="margin-top:12px;min-height:220px">
        @foreach ($tables as $table)
            <a href="{{ route('admin.tables.edit', $table) }}" class="table-seat {{ $table->status }}" title="{{ $table->zone }} · {{ $table->capacity }} seats">{{ $table->code }}</a>
        @endforeach
    </div>
</section>

<section class="card" style="margin-top:12px">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Zone</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tables as $table)
                    <tr>
                        <td><strong>{{ $table->code }}</strong></td>
                        <td>{{ $table->zone }}</td>
                        <td>{{ $table->capacity }}</td>
                        <td><span class="pill {{ $table->status === 'available' ? 'slate' : ($table->status === 'ready' ? 'green' : 'amber') }}">{{ ucfirst($table->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.tables.edit', $table) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.tables.destroy', $table) }}" onsubmit="return confirm('Delete table?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
