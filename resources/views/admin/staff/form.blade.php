@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Staff' : 'Edit Staff')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Staff' : 'Edit Staff' }}</h1>
        <p>Staff accounts can sign in and appear in POS waiter lists when active.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.staff.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.staff.store') : route('admin.staff.update', $member) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Full name<input class="field" name="name" value="{{ old('name', $member->name) }}" required></label>
            <label>Email<input class="field" type="email" name="email" value="{{ old('email', $member->email) }}" required></label>
            <label>Phone<input class="field" name="phone" value="{{ old('phone', $member->phone) }}"></label>
            <label>Job title<input class="field" name="job_title" value="{{ old('job_title', $member->job_title) }}"></label>
            <label>Role
                <select class="field" name="role_id">
                    <option value="">Unassigned</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id', $member->role_id) === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Status
                <select class="field" name="status" required>
                    <option value="active" @selected(old('status', $member->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $member->status) === 'inactive')>Inactive</option>
                </select>
            </label>
            <label>Hired on<input class="field" type="date" name="hired_on" value="{{ old('hired_on', optional($member->hired_on)->format('Y-m-d')) }}"></label>
            <label>Password<input class="field" type="password" name="password" {{ $mode === 'create' ? 'required' : '' }} placeholder="{{ $mode === 'edit' ? 'Leave blank to keep' : '' }}"></label>
            <label>Confirm password<input class="field" type="password" name="password_confirmation" {{ $mode === 'create' ? 'required' : '' }}></label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="3">{{ old('notes', $member->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Staff</button></div>
    </form>
</section>
@endsection
