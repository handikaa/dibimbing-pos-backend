<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Expected: ['token' => string, 'user' => User]
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['user']->id,
            'name' => $this['user']->name,
            'email' => $this['user']->email,
            'phone' => $this['user']->phone,
            'is_active' => $this['user']->is_active,
            'role' => $this['user']->getRoleNames()->first(),
            'token' => $this['token'],
            // 'permissions' => $this['user']->getAllPermissions()
            //     ->pluck('name')
            //     ->values(),

        ];
    }
}
