<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Default Owner',
                'password' => 'password',
                'phone' => '080000000000',
                'is_active' => true,
            ]
        );
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Default Admin',
                'password' => 'password',
                'phone' => '080000000001',
                'is_active' => true,
            ]
        );
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Default Cashier',
                'password' => 'password',
                'phone' => '080000000002',
                'is_active' => true,
            ]
        );

        if (! $owner->hasRole('OWNER')) {
            $owner->assignRole('OWNER');
        }
        if (! $admin->hasRole('ADMIN')) {
            $admin->assignRole('ADMIN');
        }
        if (! $cashier->hasRole('CASHIER')) {
            $cashier->assignRole('CASHIER');
        }
    }
}