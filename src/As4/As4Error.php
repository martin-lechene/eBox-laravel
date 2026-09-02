<?php

namespace Ebox\Enterprise\As4;

/**
 * A single ebMS3 Error entry from an eb:ErrorList.
 */
final class As4Error
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $severity = null,
        public readonly ?string $category = null,
        public readonly ?string $refToMessageInError = null,
        public readonly ?string $description = null,
        public readonly ?string $errorDetail = null,
    ) {}
}
