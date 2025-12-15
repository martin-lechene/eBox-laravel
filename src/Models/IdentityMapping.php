<?php

namespace Ebox\Enterprise\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour le mapping des identités belges
 */
class IdentityMapping extends Model
{
    protected $table = 'identity_mappings';
    
    protected $fillable = [
        'identifier',
        'type',
        'name',
        'email',
        'phone',
        'address',
        'is_validated',
        'validated_at',
        'last_verified_at',
        'cached_data',
    ];
    
    protected $casts = [
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'cached_data' => 'array',
    ];
    
    public function scopeByIdentifier($query, string $identifier, string $type)
    {
        return $query->where('identifier', $identifier)
                    ->where('type', $type);
    }
    
    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }
}

