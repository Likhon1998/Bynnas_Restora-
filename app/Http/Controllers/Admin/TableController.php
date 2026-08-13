<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = RestaurantTable::orderBy('code')->get();
        $tables->each->ensureQrToken();
        $legend = collect(['seated', 'ordered', 'preparing', 'ready', 'waiting', 'available'])
            ->map(fn ($status) => [
                'label' => ucfirst($status),
                'count' => $tables->where('status', $status)->count(),
                'status' => $status,
            ]);

        return view('admin.tables.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('tables'),
            'icons' => AdminNav::icons(),
            'tables' => $tables,
            'legend' => $legend,
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('tables'),
            'icons' => AdminNav::icons(),
            'table' => new RestaurantTable(['capacity' => 4, 'zone' => 'Main Hall', 'status' => 'available']),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RestaurantTable::create($this->validated($request));

        return redirect()->route('admin.tables.index')->with('success', 'Table created.');
    }

    public function edit(RestaurantTable $table): View
    {
        return view('admin.tables.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('tables'),
            'icons' => AdminNav::icons(),
            'table' => $table,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $table->update($this->validated($request, $table->id));

        return redirect()->route('admin.tables.index')->with('success', 'Table updated.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Table deleted.');
    }

    public function qr(RestaurantTable $table): View
    {
        $table->ensureQrToken();

        return view('admin.tables.qr', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('tables'),
            'icons' => AdminNav::icons(),
            'table' => $table->fresh(),
        ]);
    }

    public function refreshQr(RestaurantTable $table): RedirectResponse
    {
        $table->refreshQrToken();

        return redirect()
            ->route('admin.tables.qr', $table)
            ->with('success', 'QR code regenerated. Print and replace the old sticker.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:restaurant_tables,code,'.($id ?? 'NULL')],
            'capacity' => ['required', 'integer', 'min:1', 'max:40'],
            'zone' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:available,seated,ordered,preparing,ready,waiting'],
        ]);
    }
}
