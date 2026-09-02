<?php

namespace Ebox\Enterprise\As4\Contracts;

use Ebox\Enterprise\As4\As4Exception;
use Ebox\Enterprise\As4\As4Response;
use Ebox\Enterprise\As4\PayloadPart;

/**
 * Pushes AS4 user messages to a receiving MSH and returns the parsed signal.
 */
interface As4ClientInterface
{
    /**
     * @param  array<string, mixed>  $context  conversation_id, message_id, timestamp,
     *                                         message_properties, original_sender, final_recipient
     *
     * @throws As4Exception on transport or non-SOAP failures
     */
    public function send(PayloadPart $payload, array $context = []): As4Response;
}
