@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="page-head">
    <div>
        <h1>Roles &amp; Permissions</h1>
        <p>Control what each staff role can access across the admin and POS.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-gold">+ Add Role</a>
    </div>
</div>

<section class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Staff</th>
                    <th>Permissions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                            <small class="muted">{{ $role->description ?: $role->slug }}{{ $role->is_system ? ' · System' : '' }}</small>
                        </td>
                        <td>{{ $role->users_count }}</td>
                        <td>
                            @foreach ($role->permissionLabels() as $label)
                                <span class="pill slate" style="margin:0 4px 4px 0;display:inline-block">{{ $label }}</span>
                            @endforeach
                        </td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                            @unless ($role->is_system)
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                    @csrf @method('DELETE')
                                    <button class="btn" type="submit">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">No roles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
