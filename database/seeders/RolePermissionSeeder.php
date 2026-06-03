<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Auth permissions
            'auth.logout',
            'profile.view',
            'profile.change_password',

            // User management
            'user.view_any',
            'user.create',
            'user.update',
            'user.deactivate',

            // Category
            'category.view_any',
            'category.create',
            'category.update',
            'category.deactivate',

            // Product
            'product.view_any',
            'product.create',
            'product.update',
            'product.deactivate',

            // Inventory
            'inventory.view_stock',
            'inventory.adjust_stock',
            'inventory.view_movement',
            'inventory.view_low_stock',

            // Session
            'session.open',
            'session.close',
            'session.view_own',
            'session.view_any',

            // POS
            'pos.open',
            'pos.checkout_cash',
            'pos.checkout_midtrans',

            // Sales
            'sales.view_any',
            'sales.view_own',
            'sales.view_detail',
            'sales.cancel_unpaid',
            'sales.reprint_receipt',

            // Payment
            'payment.view_status',
            'payment.midtrans_create',

            // Dashboard
            'dashboard.view_summary',

            // Settings
            'settings.view',
            'settings.update',

            // Rack
            'rack.view_any',
            'rack.create',
            'rack.update',
            'rack.deactivate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create Roles
        $owner = Role::firstOrCreate([
            'name' => 'OWNER',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'ADMIN',
            'guard_name' => 'web',
        ]);

        $cashier = Role::firstOrCreate([
            'name' => 'CASHIER',
            'guard_name' => 'web',
        ]);

        // ✅ OWNER: All permissions
        $owner->syncPermissions($permissions);

        // ✅ ADMIN: Most permissions (not settings.update)
        $admin->syncPermissions([
            'auth.logout',
            'profile.view',
            'profile.change_password',
            'user.view_any',
            'user.create',
            'user.update',
            'user.deactivate',
            'category.view_any',
            'category.create',
            'category.update',
            'category.deactivate',
            'product.view_any',
            'product.create',
            'product.update',
            'product.deactivate',
            'inventory.view_stock',
            'inventory.adjust_stock',
            'inventory.view_movement',
            'inventory.view_low_stock',
            'session.open',
            'session.close',
            'session.view_own',
            'session.view_any',
            'pos.open',
            'pos.checkout_cash',
            'pos.checkout_midtrans',
            'sales.view_any',
            'sales.view_own',
            'sales.view_detail',
            'sales.cancel_unpaid',
            'sales.reprint_receipt',
            'payment.view_status',
            'payment.midtrans_create',
            'dashboard.view_summary',
            'settings.view',
            'rack.view_any',
            'rack.create',
            'rack.update',
            'rack.deactivate',
        ]);

        // ✅ CASHIER: Limited permissions
        $cashier->syncPermissions([
            'auth.logout',
            'profile.view',
            'profile.change_password',
            'category.view_any',
            'product.view_any',
            'inventory.view_stock',
            'session.open',
            'session.close',
            'session.view_own',
            'pos.open',
            'pos.checkout_cash',
            'pos.checkout_midtrans',
            'sales.view_own',
            'sales.view_detail',
            'sales.cancel_unpaid',
            'sales.reprint_receipt',
            'payment.view_status',
            'payment.midtrans_create',
            'settings.view',
        ]);

    }
}