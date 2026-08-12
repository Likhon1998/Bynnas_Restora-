<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'permissions', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function permissionCatalog(): array
    {
        return [
            'dashboard.view' => 'View dashboard',
            'pos.access' => 'Access POS',
            'menu-items.manage' => 'Manage menu items',
            'orders.manage' => 'Manage orders',
            'reservations.manage' => 'Manage reservations',
            'tables.manage' => 'Manage tables',
            'customers.manage' => 'Manage customers',
            'crm.manage' => 'Manage CRM & loyalty',
            'inventory.manage' => 'Manage inventory',
            'finance.view' => 'View finance modules',
            'expenses.manage' => 'Manage expenses',
            'tax.manage' => 'Manage tax settings',
            'staff.manage' => 'Manage staff',
            'roles.manage' => 'Manage roles & permissions',
            'settings.manage' => 'Manage site settings',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function permissionLabels(): array
    {
        $catalog = self::permissionCatalog();
        $permissions = $this->permissions ?? [];

        if (in_array('*', $permissions, true)) {
            return ['Full access'];
        }

        return collect($permissions)
            ->map(fn ($key) => $catalog[$key] ?? $key)
            ->values()
            ->all();
    }
}
