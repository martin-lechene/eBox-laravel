<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception levée lorsqu'un registre n'est pas trouvé
 */
class RegistryNotFoundException extends EboxException
{
    public function __construct(string $message = "Registre non trouvé", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}

