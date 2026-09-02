<?php

namespace Ebox\Enterprise\As4;

/**
 * An assembled AS4 push message: signed SOAP envelope plus MIME payload parts.
 */
final class As4Message
{
    /** @var array<int, PayloadPart> */
    private array $parts = [];

    public function __construct(
        public readonly string $soapXml,
        private readonly string $messageId,
        private readonly ?string $refToMessageId = null,
        public readonly ?string $conversationId = null,
    ) {}

    public function addPart(PayloadPart $part): self
    {
        $this->parts[] = $part;

        return $this;
    }

    /**
     * @return array<int, PayloadPart>
     */
    public function parts(): array
    {
        return $this->parts;
    }

    public function messageId(): string
    {
        return $this->messageId;
    }

    public function refToMessageId(): ?string
    {
        return $this->refToMessageId;
    }

    public function conversationId(): ?string
    {
        return $this->conversationId;
    }
}
