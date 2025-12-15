<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception levée lorsqu'une identité belge est invalide
 */
class InvalidIdentityException extends EboxException
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}

