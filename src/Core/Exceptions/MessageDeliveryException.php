<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception thrown when message delivery fails
 */
class MessageDeliveryException extends EboxException
{
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

