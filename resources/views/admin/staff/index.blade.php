@extends('admin.layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="page-head">
    <div>
        <h1>Staff Management</h1>
        <p>Team accounts connected to Roles, POS waiters, and dashboard staffing stats.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.roles.index') }}" class="btn">Roles</a>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-gold">+ Add Staff</a>
    </div>
</div>

<section style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
    <div class="card" style="padding:16px"><small class="muted">Total staff</small><strong style="display:block;margin-top:6px;font-size:1.2rem">{{ $stats['total'] }}</strong></div>
    <div class="card" style="padding:16px"><small class="muted">Active</small><strong style="display:block;margin-top:6px;font-size:1.2rem">{{ $stats['active'] }}</strong></div>
    <div class="card" style="padding:16px"><small class="muted">Inactive</small><strong style="display:block;margin-top:6px;font-size:1.2rem">{{ $stats['inactive'] }}</strong></div>
</section>

<section class="card">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, email, phone...">
        <select class="field" name="role_id">
            <option value="">All roles</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((string) $filters['role_id'] === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        <select class="field" name="status">
            <option value="">All statuses</option>
            <option value="active" @selected($filters['status'] === 'active')>Active</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
        </select>
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Hired</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staff as $member)
                    <tr>
                        <td>
                            <strong>{{ $member->name }}</strong>
                            <small class="muted">{{ $member->job_title ?: '—' }} · {{ $member->email }}</small>
                        </td>
                        <td>{{ $member->roleLabel() }}</td>
                        <td>{{ $member->phone ?: '—' }}</td>
                        <td>{{ optional($member->hired_on)->format('M d, Y') ?: '—' }}</td>
                        <td><span class="pill {{ $member->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($member->status) }}</span></td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                            @if ($member->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Delete this staff member?')">
                                    @csrf @method('DELETE')
                                    <button class="btn" type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No staff found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $staff->links('pagination.admin') }}</div>
</section>
@endsection
