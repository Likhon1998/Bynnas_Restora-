@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Expense' : 'Edit Expense')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Expense' : 'Edit Expense' }}</h1>
        <p>Expenses reduce net profit in Accounting and appear in Reports.</p>
    </div>
    <div class="page-head-actions"><a href="{{ route('admin.expenses.index') }}" class="btn">Back</a></div>
</div>

<section class="card form-card">
    <form method="POST" action="{{ $mode === 'create' ? route('admin.expenses.store') : route('admin.expenses.update', $expense) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif
        <div class="form-grid">
            <label>Title<input class="field" name="title" value="{{ old('title', $expense->title) }}" required></label>
            <label>Category
                <select class="field" name="category" required>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Amount (৳)<input class="field" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required></label>
            <label>Date<input class="field" type="date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? $expense->expense_date) }}" required></label>
            <label>Payment method
                <select class="field" name="payment_method" required>
                    @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank' => 'Bank Transfer', 'online' => 'Online'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_method', $expense->payment_method) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Vendor<input class="field" name="vendor" value="{{ old('vendor', $expense->vendor) }}"></label>
            <label>Reference<input class="field" name="reference" value="{{ old('reference', $expense->reference) }}" placeholder="Auto if empty"></label>
            <label class="span-2">Notes<textarea class="field" name="notes" rows="3">{{ old('notes', $expense->notes) }}</textarea></label>
        </div>
        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Expense</button></div>
    </form>
</section>
@endsection
