<?php

namespace Ebox\Enterprise\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for e-Box message audit logs
 */
class MessageAuditLog extends Model
{
    protected $table = 'message_audit_logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'ebox_message_id',
        'action',
        'actor_identifier',
        'actor_type',
        'ip_address',
        'user_agent',
        'details',
    ];
    
    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];
    
    public function message()
    {
        return $this->belongsTo(EboxMessage::class, 'ebox_message_id');
    }
    
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    
    public function scopeByActor($query, string $identifier, string $type)
    {
        return $query->where('actor_identifier', $identifier)
                    ->where('actor_type', $type);
    }
}

