<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];

        return [
            'token' => $this->resource['token'],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'role' => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()
                    ->pluck('name')
                    ->values(),
            ],
        ];
    }
}
