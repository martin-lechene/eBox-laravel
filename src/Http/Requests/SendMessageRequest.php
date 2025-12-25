<?php

namespace Ebox\Enterprise\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ebox\Enterprise\Core\Enums\IdentityType;
use Ebox\Enterprise\Core\Enums\ConfidentialityLevel;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'sender_identifier' => ['required', 'string', 'max:50'],
            'sender_type' => ['required', 'in:CBE,NRN'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'recipient_identifier' => ['required', 'string', 'max:50'],
            'recipient_type' => ['required', 'in:CBE,NRN'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'message_type' => ['nullable', 'string', 'max:50'],
            'confidentiality_level' => ['nullable', 'in:' . implode(',', array_column(ConfidentialityLevel::cases(), 'value'))],
            'integration_profile' => ['nullable', 'in:central,private'],
            'metadata' => ['nullable', 'array'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'sender_identifier.required' => 'Sender identifier is required',
            'sender_type.required' => 'Sender identity type is required',
            'recipient_identifier.required' => 'Recipient identifier is required',
            'recipient_type.required' => 'Recipient identity type is required',
            'subject.required' => 'Message subject is required',
            'body.required' => 'Message content is required',
        ];
    }
}

