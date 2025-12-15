<?php

namespace Ebox\Enterprise\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ebox\Enterprise\Core\Enums\MessageStatus;
use Ebox\Enterprise\Core\Enums\ConfidentialityLevel;
use Ramsey\Uuid\Uuid;

/**
 * Modèle principal pour les messages e-Box
 */
class EboxMessage extends Model
{
    use SoftDeletes;
    
    protected $table = 'ebox_messages';
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'sender_identifier',
        'sender_type',
        'sender_name',
        'recipient_identifier',
        'recipient_type',
        'recipient_name',
        'subject',
        'body',
        'message_type',
        'confidentiality_level',
        'message_registry_id',
        'registry_endpoint',
        'status',
        'external_message_id',
        'metadata',
        'encryption_key_id',
    ];
    
    protected $casts = [
        'status' => MessageStatus::class,
        'confidentiality_level' => ConfidentialityLevel::class,
        'metadata' => 'array',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }
    
    public function registry()
    {
        return $this->belongsTo(MessageRegistry::class, 'message_registry_id');
    }
    
    public function auditLogs()
    {
        return $this->hasMany(MessageAuditLog::class, 'ebox_message_id');
    }
    
    public function failedDeliveries()
    {
        return $this->hasMany(FailedMessageDelivery::class, 'ebox_message_id');
    }
    
    public function scopeWithHighConfidentiality($query)
    {
        return $query->where('confidentiality_level', ConfidentialityLevel::MAXIMUM);
    }
    
    public function scopeBySender($query, string $identifier, string $type)
    {
        return $query->where('sender_identifier', $identifier)
                    ->where('sender_type', $type);
    }
    
    public function scopeByRecipient($query, string $identifier, string $type)
    {
        return $query->where('recipient_identifier', $identifier)
                    ->where('recipient_type', $type);
    }
    
    public function markAsRead(): bool
    {
        return $this->update([
            'status' => MessageStatus::READ,
            'read_at' => now(),
            'status_updated_at' => now(),
        ]);
    }
    
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => MessageStatus::DELIVERED,
            'delivered_at' => now(),
            'status_updated_at' => now(),
        ]);
    }
}

