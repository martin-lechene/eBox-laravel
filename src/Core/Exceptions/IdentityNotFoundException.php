<?php

namespace Ebox\Enterprise\Core\Exceptions;

/**
 * Exception levée lorsqu'une identité belge n'est pas trouvée
 */
class IdentityNotFoundException extends EboxException
{
    public function __construct(string $identifier, string $type)
    {
        parent::__construct(
            "Identité {$type} non trouvée : {$identifier}",
            404
        );
    }
}

