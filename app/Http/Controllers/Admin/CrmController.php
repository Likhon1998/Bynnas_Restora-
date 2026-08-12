<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CrmController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_customers' => Customer::count(),
            'members' => Customer::whereIn('membership_tier', ['silver', 'gold', 'platinum'])->count(),
            'points_pool' => Customer::sum('loyalty_points'),
            'redeemed' => abs((int) LoyaltyTransaction::where('type', 'redeem')->sum('points')),
        ];

        $topMembers = Customer::orderByDesc('loyalty_points')->limit(8)->get();
        $recent = LoyaltyTransaction::with('customer')->latest()->limit(12)->get();

        return view('admin.crm.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('crm'),
            'icons' => AdminNav::icons(),
            'stats' => $stats,
            'topMembers' => $topMembers,
            'recent' => $recent,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'type' => ['required', 'in:earn,redeem,adjust'],
            'points' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data['customer_id']);
            $points = (int) $data['points'];
            $delta = $data['type'] === 'redeem' ? -$points : $points;

            if ($data['type'] === 'redeem' && $customer->loyalty_points < $points) {
                abort(422, 'Not enough loyalty points.');
            }

            $customer->loyalty_points = max(0, $customer->loyalty_points + $delta);
            $customer->save();

            LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'type' => $data['type'],
                'points' => $data['type'] === 'redeem' ? -$points : $points,
                'description' => $data['description'] ?? ucfirst($data['type']).' points',
            ]);
        });

        return redirect()->route('admin.crm.index')->with('success', 'Loyalty transaction saved.');
    }
}
