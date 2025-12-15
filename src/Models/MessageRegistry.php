<?php

namespace Ebox\Enterprise\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les registres de messages e-Box
 */
class MessageRegistry extends Model
{
    protected $table = 'message_registries';
    
    protected $fillable = [
        'name',
        'type',
        'endpoint_url',
        'api_key',
        'api_secret',
        'supports_high_confidentiality',
        'supports_private_registry',
        'description',
        'is_active',
        'priority',
    ];
    
    protected $casts = [
        'supports_high_confidentiality' => 'boolean',
        'supports_private_registry' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
    
    public function messages()
    {
        return $this->hasMany(EboxMessage::class, 'message_registry_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    public function scopeWithHighConfidentiality($query)
    {
        return $query->where('supports_high_confidentiality', true);
    }
}

