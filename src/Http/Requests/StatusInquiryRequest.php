<?php

namespace Ebox\Enterprise\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'message_id' => ['required', 'string'],
        ];
    }
}

