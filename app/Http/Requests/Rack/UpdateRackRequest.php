<?php

namespace App\Http\Requests\Rack;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRackRequest extends FormRequest
{
    /**
     * Basic validation - permission check dilakukan di UseCase
     */
    public function authorize(): bool
    {
        // Cukup check authenticated
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }
}
