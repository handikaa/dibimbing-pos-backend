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

        if (! $owner->hasRole('OWNER')) {
            $owner->assignRole('OWNER');
        }
    }
}