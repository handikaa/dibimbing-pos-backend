<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use Illuminate\Database\Seeder;

class RackSeeder extends Seeder
{
    public function run(): void
    {
        $racks = [
            [
                'code' => 'A1',
                'name' => 'Rak Utama Depan',
                'description' => 'Untuk best-selling items',
                'is_active' => true,
            ],
            [
                'code' => 'A2',
                'name' => 'Rak Kedua Depan',
                'description' => 'Untuk seasonal items',
                'is_active' => true,
            ],
            [
                'code' => 'B1',
                'name' => 'Rak Belakang',
                'description' => 'Untuk stock buffer',
                'is_active' => true,
            ],
            [
                'code' => 'FRZ',
                'name' => 'Freezer',
                'description' => 'Lemari pendingin',
                'is_active' => true,
            ],
        ];

        foreach ($racks as $rack) {
            Rack::create($rack);
        }
    }
}