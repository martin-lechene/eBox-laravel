<?php

namespace Ebox\Enterprise\As4;

use Ebox\Enterprise\Core\Exceptions\EboxException;

/**
 * Raised for AS4 / ebMS3 transport or building failures.
 */
class As4Exception extends EboxException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?As4Response $response = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
