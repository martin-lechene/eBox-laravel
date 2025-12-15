<?php

namespace Ebox\Enterprise\Listeners;

use Ebox\Enterprise\Events\MessageSent;
use Ebox\Enterprise\Events\MessageDelivered;
use Ebox\Enterprise\Events\MessageRead;
use Ebox\Enterprise\Services\Audit\AuditLogger;

class LogMessageActivity
{
    private AuditLogger $auditLogger;
    
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }
    
    public function handle(MessageSent $event): void
    {
        $this->auditLogger->logMessageSent($event->message);
    }
    
    public function handleDelivered(MessageDelivered $event): void
    {
        $this->auditLogger->logMessageDelivered($event->message);
    }
    
    public function handleRead(MessageRead $event): void
    {
        $this->auditLogger->logMessageRead($event->message);
    }
}

