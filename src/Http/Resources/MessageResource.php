<?php

namespace Ebox\Enterprise\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_message_id' => $this->external_message_id,
            'sender' => [
                'identifier' => $this->sender_identifier,
                'type' => $this->sender_type,
                'name' => $this->sender_name,
            ],
            'recipient' => [
                'identifier' => $this->recipient_identifier,
                'type' => $this->recipient_type,
                'name' => $this->recipient_name,
            ],
            'subject' => $this->subject,
            'body' => $this->body,
            'message_type' => $this->message_type,
            'confidentiality_level' => $this->confidentiality_level->value,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'read_at' => $this->read_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'registry' => $this->whenLoaded('registry', function () {
                return [
                    'id' => $this->registry->id,
                    'name' => $this->registry->name,
                    'type' => $this->registry->type,
                ];
            }),
        ];
    }
}

