<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Reservation;

class AdminNav
{
    public static function icons(): array
    {
        return [
            'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
            'pos' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
            'menu' => '<path d="M3 2h18v4H3z"/><path d="M3 10h18v4H3z"/><path d="M3 18h18v4H3z"/>',
            'orders' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'reservations' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
            'tables' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'customers' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 0v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'crm' => '<path d="M19 14c1.5-1.3 2.5-3 2.5-5A5.5 5.5 0 0 0 12 5.1 5.5 5.5 0 0 0 2.5 9c0 2 1 3.7 2.5 5l7 7Z"/>',
            'inventory' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'recipes' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
            'suppliers' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
            'po' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v6h6"/>',
            'transfers' => '<path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/>',
            'wastage' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h18a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'accounting' => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
            'reports' => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
            'expenses' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
            'tax' => '<path d="M19 5 5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
            'staff' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/>',
            'roles' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>',
        ];
    }

    public static function groups(?string $active = null): array
    {
        $openOrders = 0;
        $upcomingReservations = 0;
        try {
            $openOrders = Order::whereNotIn('status', ['completed', 'cancelled'])->count();
            $upcomingReservations = Reservation::whereIn('status', ['pending', 'confirmed'])
                ->where('reserved_at', '>=', now()->startOfDay())
                ->count();
        } catch (\Throwable) {
            // tables may not exist yet during early boot/migrate
        }

        return [
            ['section' => null, 'items' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'key' => 'dashboard'],
            ]],
            ['section' => 'Operations', 'items' => [
                ['label' => 'POS', 'icon' => 'pos', 'route' => 'admin.pos.index', 'key' => 'pos', 'target' => '_blank'],
                ['label' => 'Menu Items', 'icon' => 'menu', 'route' => 'admin.menu-items.index', 'key' => 'menu-items'],
                ['label' => 'Orders', 'icon' => 'orders', 'route' => 'admin.orders.index', 'key' => 'orders', 'badge' => $openOrders ?: null],
                ['label' => 'Reservations', 'icon' => 'reservations', 'route' => 'admin.reservations.index', 'key' => 'reservations', 'badge' => $upcomingReservations ?: null],
                ['label' => 'Table Management', 'icon' => 'tables', 'route' => 'admin.tables.index', 'key' => 'tables'],
                ['label' => 'Customers', 'icon' => 'customers', 'route' => 'admin.customers.index', 'key' => 'customers'],
                ['label' => 'CRM & Loyalty', 'icon' => 'crm', 'route' => 'admin.crm.index', 'key' => 'crm'],
            ]],
            ['section' => 'Inventory', 'items' => [
                ['label' => 'Inventory', 'icon' => 'inventory', 'route' => 'admin.inventory.index', 'key' => 'inventory'],
                ['label' => 'Recipes (BOM)', 'icon' => 'recipes', 'route' => 'admin.recipes.index', 'key' => 'recipes'],
                ['label' => 'Suppliers', 'icon' => 'suppliers', 'route' => 'admin.suppliers.index', 'key' => 'suppliers'],
                ['label' => 'Purchase Orders', 'icon' => 'po', 'route' => 'admin.purchase-orders.index', 'key' => 'purchase-orders'],
                ['label' => 'Stock Transfers', 'icon' => 'transfers', 'route' => 'admin.stock-transfers.index', 'key' => 'stock-transfers'],
                ['label' => 'Wastage & Variance', 'icon' => 'wastage', 'route' => 'admin.wastage.index', 'key' => 'wastage'],
            ]],
            ['section' => 'Finance', 'items' => [
                ['label' => 'Accounting', 'icon' => 'accounting', 'route' => null, 'key' => 'accounting'],
                ['label' => 'Reports', 'icon' => 'reports', 'route' => null, 'key' => 'reports'],
                ['label' => 'Expenses', 'icon' => 'expenses', 'route' => null, 'key' => 'expenses'],
                ['label' => 'Tax Management', 'icon' => 'tax', 'route' => null, 'key' => 'tax'],
            ]],
            ['section' => 'Staff & Settings', 'items' => [
                ['label' => 'Staff Management', 'icon' => 'staff', 'route' => null, 'key' => 'staff'],
                ['label' => 'Roles & Permissions', 'icon' => 'roles', 'route' => null, 'key' => 'roles'],
                ['label' => 'Settings', 'icon' => 'settings', 'route' => null, 'key' => 'settings'],
            ]],
        ];
    }

    public static function withActive(?string $active = null): array
    {
        $groups = self::groups($active);

        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                $item['active'] = ($item['key'] ?? '') === $active;
            }
        }

        return $groups;
    }
}
