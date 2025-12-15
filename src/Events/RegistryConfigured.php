<?php

namespace Ebox\Enterprise\Events;

use Ebox\Enterprise\Models\MessageRegistry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistryConfigured
{
    use Dispatchable, SerializesModels;
    
    public MessageRegistry $registry;
    
    public function __construct(MessageRegistry $registry)
    {
        $this->registry = $registry;
    }
}

