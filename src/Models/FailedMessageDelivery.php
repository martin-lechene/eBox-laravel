<?php

namespace Ebox\Enterprise\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les échecs de livraison de messages
 */
class FailedMessageDelivery extends Model
{
    protected $table = 'failed_message_deliveries';
    
    protected $fillable = [
        'ebox_message_id',
        'registry_id',
        'error_code',
        'error_message',
        'retry_count',
        'next_retry_at',
    ];
    
    protected $casts = [
        'retry_count' => 'integer',
        'next_retry_at' => 'datetime',
    ];
    
    public function message()
    {
        return $this->belongsTo(EboxMessage::class, 'ebox_message_id');
    }
    
    public function registry()
    {
        return $this->belongsTo(MessageRegistry::class, 'registry_id');
    }
    
    public function scopeRetryable($query)
    {
        return $query->where('next_retry_at', '<=', now())
                    ->where('retry_count', '<', 3);
    }
}

