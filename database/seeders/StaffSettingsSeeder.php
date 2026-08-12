<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full access to all modules',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        $managerRole = Role::query()->updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Operations, inventory, and finance oversight',
                'permissions' => [
                    'dashboard.view', 'pos.access', 'menu-items.manage', 'orders.manage',
                    'reservations.manage', 'tables.manage', 'customers.manage', 'crm.manage',
                    'inventory.manage', 'finance.view', 'expenses.manage', 'tax.manage', 'settings.manage',
                ],
                'is_system' => true,
            ]
        );

        $cashierRole = Role::query()->updateOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'Cashier',
                'description' => 'POS and order handling',
                'permissions' => [
                    'dashboard.view', 'pos.access', 'orders.manage', 'customers.manage', 'reservations.manage',
                ],
                'is_system' => true,
            ]
        );

        $waiterRole = Role::query()->updateOrCreate(
            ['slug' => 'waiter'],
            [
                'name' => 'Waiter',
                'description' => 'Floor service and table support',
                'permissions' => [
                    'dashboard.view', 'pos.access', 'orders.manage', 'reservations.manage', 'tables.manage',
                ],
                'is_system' => true,
            ]
        );

        Role::query()->updateOrCreate(
            ['slug' => 'chef'],
            [
                'name' => 'Chef',
                'description' => 'Kitchen and recipe access',
                'permissions' => [
                    'dashboard.view', 'menu-items.manage', 'orders.manage', 'inventory.manage',
                ],
                'is_system' => true,
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bynnasrestora.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'phone' => '+8801700000001',
                'job_title' => 'Owner / Admin',
                'status' => 'active',
                'hired_on' => now()->subYears(2)->toDateString(),
            ]
        );

        $staff = [
            ['name' => 'Rahim Uddin', 'email' => 'rahim@bynnasrestora.com', 'role' => $waiterRole, 'job_title' => 'Senior Waiter', 'phone' => '+8801711112201'],
            ['name' => 'Karim Ali', 'email' => 'karim@bynnasrestora.com', 'role' => $waiterRole, 'job_title' => 'Waiter', 'phone' => '+8801711112202'],
            ['name' => 'Nusrat Jahan', 'email' => 'nusrat@bynnasrestora.com', 'role' => $cashierRole, 'job_title' => 'Cashier', 'phone' => '+8801711112203'],
            ['name' => 'Mina Rahman', 'email' => 'mina@bynnasrestora.com', 'role' => $managerRole, 'job_title' => 'Floor Manager', 'phone' => '+8801711112204'],
            ['name' => 'Abdul Chef', 'email' => 'chef@bynnasrestora.com', 'role' => Role::where('slug', 'chef')->first(), 'job_title' => 'Head Chef', 'phone' => '+8801711112205'],
        ];

        foreach ($staff as $i => $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                    'role_id' => $row['role']?->id,
                    'phone' => $row['phone'],
                    'job_title' => $row['job_title'],
                    'status' => 'active',
                    'hired_on' => now()->subMonths(6 + $i)->toDateString(),
                ]
            );
        }

        // Keep seeded admin elevated
        $admin->update(['role_id' => $adminRole->id, 'status' => 'active']);

        SiteSetting::current();
    }
}
