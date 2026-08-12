<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $category = trim((string) $request->get('category', ''));
        $from = $request->date('from')?->toDateString();
        $to = $request->date('to')?->toDateString();

        $expenses = Expense::query()
            ->with('recorder')
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('title', 'ilike', "%{$q}%")
                    ->orWhere('vendor', 'ilike', "%{$q}%")
                    ->orWhere('reference', 'ilike', "%{$q}%");
            }))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($from, fn ($query) => $query->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('expense_date', '<=', $to))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $total = (float) Expense::query()
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($from, fn ($query) => $query->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('expense_date', '<=', $to))
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('title', 'ilike', "%{$q}%")
                    ->orWhere('vendor', 'ilike', "%{$q}%")
                    ->orWhere('reference', 'ilike', "%{$q}%");
            }))
            ->sum('amount');

        return view('admin.finance.expenses.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('expenses'),
            'icons' => AdminNav::icons(),
            'expenses' => $expenses,
            'categories' => Expense::categories(),
            'total' => $total,
            'filters' => [
                'q' => $q,
                'category' => $category,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.finance.expenses.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('expenses'),
            'icons' => AdminNav::icons(),
            'expense' => new Expense([
                'expense_date' => now()->toDateString(),
                'category' => 'other',
                'payment_method' => 'cash',
            ]),
            'categories' => Expense::categories(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['recorded_by'] = auth()->id();
        if (empty($data['reference'])) {
            $data['reference'] = 'EXP-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        }

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        return view('admin.finance.expenses.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('expenses'),
            'icons' => AdminNav::icons(),
            'expense' => $expense,
            'categories' => Expense::categories(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validated($request));

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:'.implode(',', array_keys(Expense::categories()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,card,bank,online'],
            'vendor' => ['nullable', 'string', 'max:160'],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
