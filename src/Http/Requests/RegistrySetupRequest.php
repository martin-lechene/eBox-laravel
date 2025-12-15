<?php

namespace Ebox\Enterprise\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrySetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:central,private'],
            'endpoint_url' => ['required', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'supports_high_confidentiality' => ['nullable', 'boolean'],
            'supports_private_registry' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}

