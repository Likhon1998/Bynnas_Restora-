<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('roles'),
            'icons' => AdminNav::icons(),
            'roles' => $roles,
            'catalog' => Role::permissionCatalog(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('roles'),
            'icons' => AdminNav::icons(),
            'role' => new Role(['permissions' => []]),
            'catalog' => Role::permissionCatalog(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $data['slug'] = Str::slug($data['name']);
        $data['is_system'] = false;

        Role::create($data);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('roles'),
            'icons' => AdminNav::icons(),
            'role' => $role,
            'catalog' => Role::permissionCatalog(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);

        if (! $role->is_system) {
            $data['slug'] = Str::slug($data['name']);
        } else {
            unset($data['name']);
        }

        if ($role->slug === 'admin') {
            $data['permissions'] = ['*'];
        }

        $role->update($data);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('success', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('success', 'Reassign staff before deleting this role.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    private function validated(Request $request, ?Role $role): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_merge(array_keys(Role::permissionCatalog()), ['*']))],
        ]);
    }
}
