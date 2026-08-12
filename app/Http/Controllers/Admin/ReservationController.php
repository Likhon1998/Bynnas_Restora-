<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $q = trim((string) $request->get('q', ''));

        $reservations = Reservation::query()
            ->with(['table', 'customer'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('guest_name', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%");
            }))
            ->orderBy('reserved_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'today' => Reservation::whereDate('reserved_at', today())->count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'confirmed' => Reservation::where('status', 'confirmed')->count(),
        ];

        return view('admin.reservations.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('reservations'),
            'icons' => AdminNav::icons(),
            'reservations' => $reservations,
            'stats' => $stats,
            'filters' => compact('status', 'q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.reservations.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('reservations'),
            'icons' => AdminNav::icons(),
            'reservation' => new Reservation([
                'reserved_at' => now()->addHour()->format('Y-m-d\\TH:i'),
                'guests' => 2,
                'status' => 'pending',
            ]),
            'tables' => RestaurantTable::orderBy('code')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Reservation::create($this->validated($request));

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation created.');
    }

    public function edit(Reservation $reservation): View
    {
        return view('admin.reservations.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('reservations'),
            'icons' => AdminNav::icons(),
            'reservation' => $reservation,
            'tables' => RestaurantTable::orderBy('code')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($this->validated($request));

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'guest_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'guests' => ['required', 'integer', 'min:1', 'max:40'],
            'reserved_at' => ['required', 'date'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'status' => ['required', 'in:pending,confirmed,seated,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
