@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Role' : 'Edit Role')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Role' : 'Edit Role' }}</h1>
        <p>Assign module permissions used by staff accounts.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.roles.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.roles.store') : route('admin.roles.update', $role) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Role name
                <input class="field" name="name" value="{{ old('name', $role->name) }}" {{ $role->is_system ? 'readonly' : 'required' }}>
            </label>
            <label class="span-2">Description
                <input class="field" name="description" value="{{ old('description', $role->description) }}">
            </label>
        </div>

        <h3 style="margin:18px 0 10px;font-size:14px">Permissions</h3>
        @if ($role->slug === 'admin')
            <p class="muted">Admin always has full access.</p>
            <input type="hidden" name="permissions[]" value="*">
        @else
            <div class="form-grid">
                @php $selected = old('permissions', $role->permissions ?? []); @endphp
                @foreach ($catalog as $key => $label)
                    <label style="display:flex;align-items:center;gap:8px">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected, true) || in_array('*', $selected, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        @endif

        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Role</button></div>
    </form>
</section>
@endsection
