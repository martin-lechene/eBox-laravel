<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use Ebox\Enterprise\As4\MessageIdGenerator;
use Ebox\Enterprise\Tests\TestCase;

class MessageIdGeneratorTest extends TestCase
{
    public function test_message_id_uses_urn_uuid_format(): void
    {
        $generator = new MessageIdGenerator;

        $this->assertMatchesRegularExpression(
            '/^urn:uuid:[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $generator->messageId()
        );
    }

    public function test_message_ids_are_unique(): void
    {
        $generator = new MessageIdGenerator;
        $ids = array_map(fn () => $generator->messageId(), range(1, 100));

        $this->assertSame(100, count(array_unique($ids)));
    }

    public function test_conversation_id_is_rfc4122_without_urn_prefix(): void
    {
        $generator = new MessageIdGenerator;

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $generator->conversationId()
        );
    }

    public function test_content_id_uses_cid_syntax(): void
    {
        $generator = new MessageIdGenerator;

        $this->assertMatchesRegularExpression('/^[0-9a-f-]+@ebox$/', $generator->contentId());
    }
}
