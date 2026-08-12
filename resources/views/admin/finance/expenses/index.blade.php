@extends('admin.layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="page-head">
    <div>
        <h1>Expenses</h1>
        <p>Track operating costs that feed Accounting and Reports.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-gold">+ Add Expense</a>
    </div>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <input class="field" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search title, vendor, ref...">
        <select class="field" name="category">
            <option value="">All categories</option>
            @foreach ($categories as $key => $label)
                <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input class="field" type="date" name="from" value="{{ $filters['from'] }}">
        <input class="field" type="date" name="to" value="{{ $filters['to'] }}">
        <button class="btn btn-solid" type="submit">Filter</button>
    </form>

    <p style="margin:0 0 12px"><strong>Filtered total:</strong> ৳ {{ number_format($total, 2) }}</p>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr>
                        <td>{{ optional($expense->expense_date)->format('M d, Y') }}</td>
                        <td>
                            <strong>{{ $expense->title }}</strong>
                            <small class="muted">{{ $expense->reference }}</small>
                        </td>
                        <td>{{ $expense->categoryLabel() }}</td>
                        <td>{{ $expense->vendor ?: '—' }}</td>
                        <td>{{ $expense->paymentLabel() }}</td>
                        <td>৳ {{ number_format((float) $expense->amount, 2) }}</td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.expenses.edit', $expense) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No expenses yet. Add one to connect costs with Accounting.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $expenses->links('pagination.admin') }}</div>
</section>
@endsection
