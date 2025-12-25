<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception thrown when a Belgian identity is invalid
 */
class InvalidIdentityException extends EboxException
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}

