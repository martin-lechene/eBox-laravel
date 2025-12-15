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
            'sender_identifier.required' => 'L\'identifiant de l\'expéditeur est requis',
            'sender_type.required' => 'Le type d\'identité de l\'expéditeur est requis',
            'recipient_identifier.required' => 'L\'identifiant du destinataire est requis',
            'recipient_type.required' => 'Le type d\'identité du destinataire est requis',
            'subject.required' => 'Le sujet du message est requis',
            'body.required' => 'Le contenu du message est requis',
        ];
    }
}

