<?php

namespace Ebox\Enterprise\As4;

/**
 * Parsed outcome of an AS4 push exchange (receipt, ebMS error or SOAP fault).
 */
final class As4Response
{
    /**
     * @param  array<int, As4Error>  $errors
     */
    public function __construct(
        public readonly As4ResponseType $type = As4ResponseType::NONE,
        public readonly ?string $messageId = null,
        public readonly ?string $refToMessageId = null,
        public readonly array $errors = [],
        public readonly ?string $receiptHash = null,
        public readonly ?string $digestMethod = null,
        public readonly ?string $faultCode = null,
        public readonly ?string $description = null,
        public readonly ?string $rawBody = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->type === As4ResponseType::RECEIPT
            || $this->type === As4ResponseType::NONE;
    }

    public function isError(): bool
    {
        return $this->type === As4ResponseType::ERROR
            || $this->type === As4ResponseType::FAULT;
    }
}
