<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception thrown when a registry is not found
 */
class RegistryNotFoundException extends EboxException
{
    public function __construct(string $message = "Registry not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}

