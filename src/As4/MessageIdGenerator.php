<?php

namespace Ebox\Enterprise\As4;

use Ramsey\Uuid\Uuid;

/**
 * Generates ebMS3 message / content identifiers conforming to RFC 4122
 * and the AS4 profile message identifier format.
 */
final class MessageIdGenerator
{
    public function __construct(
        private string $prefix = 'urn:uuid:',
        private string $cidDomain = 'ebox',
    ) {}

    /**
     * ebMS3 MessageId, e.g. "urn:uuid:6a1d...".
     */
    public function messageId(): string
    {
        return $this->prefix.Uuid::uuid4()->toString();
    }

    /**
     * ConversationId (RFC 4122 form, no URN prefix).
     */
    public function conversationId(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * RFC 2392 Content-ID local part, e.g. "b97c...@ebox".
     */
    public function contentId(): string
    {
        return Uuid::uuid4()->toString().'@'.$this->cidDomain;
    }
}
