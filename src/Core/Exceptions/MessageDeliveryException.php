<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception levée lors d'un échec de livraison de message
 */
class MessageDeliveryException extends EboxException
{
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

