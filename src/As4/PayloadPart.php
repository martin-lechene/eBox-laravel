<?php

namespace Ebox\Enterprise\As4;

/**
 * A single business payload attached to an AS4 user message.
 */
final class PayloadPart
{
    public function __construct(
        public readonly string $content,
        public readonly string $mimeType = As4Constants::MIME_XML,
        public readonly ?string $characterSet = 'utf-8',
        public readonly ?string $name = null,
        public readonly bool $compressed = false,
        public readonly ?string $cid = null,
    ) {}

    public function withCid(string $cid): self
    {
        return new self($this->content, $this->mimeType, $this->characterSet, $this->name, $this->compressed, $cid);
    }

    public function withCompression(bool $compressed): self
    {
        return new self($this->content, $this->mimeType, $this->characterSet, $this->name, $compressed, $this->cid);
    }

    /**
     * The bytes actually carried in the MIME part (gzip applied when requested).
     */
    public function encodedContent(): string
    {
        return $this->compressed ? gzencode($this->content, 9, FORCE_GZIP) : $this->content;
    }
}
