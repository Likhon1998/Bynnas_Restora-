<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $roleId = $request->get('role_id');
        $status = trim((string) $request->get('status', ''));

        $staff = User::query()
            ->with('role')
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%")
                    ->orWhere('job_title', 'ilike', "%{$q}%");
            }))
            ->when($roleId, fn ($query) => $query->where('role_id', $roleId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.staff.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('staff'),
            'icons' => AdminNav::icons(),
            'staff' => $staff,
            'roles' => Role::orderBy('name')->get(),
            'filters' => [
                'q' => $q,
                'role_id' => $roleId,
                'status' => $status,
            ],
            'stats' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('staff'),
            'icons' => AdminNav::icons(),
            'member' => new User([
                'status' => 'active',
                'hired_on' => now()->toDateString(),
            ]),
            'roles' => Role::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created.');
    }

    public function edit(User $staff): View
    {
        return view('admin.staff.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('staff'),
            'icons' => AdminNav::icons(),
            'member' => $staff,
            'roles' => Role::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        if ($staff->id === auth()->id()) {
            return back()->with('success', 'You cannot delete your own account.');
        }

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted.');
    }

    private function validated(Request $request, ?User $staff): array
    {
        $passwordRules = $staff
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($staff?->id)],
            'password' => $passwordRules,
            'role_id' => ['nullable', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive'],
            'hired_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
