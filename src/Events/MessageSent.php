<?php

namespace Ebox\Enterprise\Events;

use Ebox\Enterprise\Models\EboxMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable, SerializesModels;
    
    public EboxMessage $message;
    
    public function __construct(EboxMessage $message)
    {
        $this->message = $message;
    }
}

