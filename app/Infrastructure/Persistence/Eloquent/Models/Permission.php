<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Spatie sudah menangani relasi roles & users
    // Bisa ditambahkan custom logic jika perlu
}
