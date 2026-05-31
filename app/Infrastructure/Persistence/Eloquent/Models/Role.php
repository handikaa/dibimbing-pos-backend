<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Spatie sudah menangani relasi permissions & users
    // Bisa ditambahkan custom logic jika perlu
}