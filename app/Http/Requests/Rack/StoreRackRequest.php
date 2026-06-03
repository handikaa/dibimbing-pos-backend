<?php

namespace App\Http\Requests\Rack;

use Illuminate\Foundation\Http\FormRequest;

class StoreRackRequest extends FormRequest
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
            'code' => 'required|string|max:50|unique:racks,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode rack wajib diisi',
            'code.unique' => 'Kode rack sudah terdaftar',
            'name.required' => 'Nama rack wajib diisi',
        ];
    }
}
