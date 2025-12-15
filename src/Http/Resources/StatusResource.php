<?php

namespace Ebox\Enterprise\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'message_id' => $this->resource['message_id'] ?? null,
            'status' => $this->resource['status'] ?? null,
            'read_at' => $this->resource['read_at'] ?? null,
            'delivered_at' => $this->resource['delivered_at'] ?? null,
            'last_updated' => $this->resource['last_updated'] ?? null,
            'source' => $this->resource['source'] ?? 'local',
        ];
    }
}

