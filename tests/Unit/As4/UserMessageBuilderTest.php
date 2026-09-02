<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Ebox\Enterprise\As4\As4Constants;
use Ebox\Enterprise\As4\MessageIdGenerator;
use Ebox\Enterprise\As4\PayloadPart;
use Ebox\Enterprise\As4\Pmode;
use Ebox\Enterprise\As4\UserMessageBuilder;
use Ebox\Enterprise\Tests\TestCase;

class UserMessageBuilderTest extends TestCase
{
    private function pmode(): Pmode
    {
        return Pmode::fromConfig([
            'pmode_id' => 'ebox-push-001',
            'from' => ['id' => 'BE0123456789', 'type' => As4Constants::PARTYID_UNREGISTERED],
            'to' => ['id' => 'BE0987654321', 'type' => As4Constants::PARTYID_UNREGISTERED],
            'service' => 'urn:example:service',
            'action' => 'Submit',
            'message_properties' => [
                ['name' => 'businessDocument', 'value' => 'invoice'],
            ],
        ]);
    }

    private function build(array $context = []): array
    {
        $pmode = $this->pmode();
        $builder = new UserMessageBuilder(new MessageIdGenerator);
        $part = new PayloadPart(
            content: '<Invoice/>',
            mimeType: 'application/xml',
            characterSet: 'utf-8',
        );

        return $builder->build($pmode, [$part], $context);
    }

    private function xpath(DOMDocument $document): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('env', As4Constants::SOAP12_NS);
        $xpath->registerNamespace('eb', As4Constants::EBMS_NS);
        $xpath->registerNamespace('wsse', As4Constants::WSSE_NS);
        $xpath->registerNamespace('wsu', As4Constants::WSU_NS);
        $xpath->registerNamespace('ds', As4Constants::DS_NS);

        return $xpath;
    }

    public function test_envelope_is_soap_12_with_empty_body(): void
    {
        [$document] = $this->build();
        $xpath = $this->xpath($document);

        $this->assertSame(1, $xpath->query('//env:Envelope')->length);
        $this->assertSame(1, $xpath->query('/env:Envelope/env:Body')->length);
        $this->assertSame(0, $xpath->query('/env:Envelope/env:Body/*')->length);
        $this->assertSame(
            As4Constants::SOAP12_NS,
            $document->documentElement->namespaceURI
        );
    }

    public function test_use_message_headers_are_present(): void
    {
        [$document] = $this->build([
            'message_id' => 'urn:uuid:11111111-1111-4111-8111-111111111111',
            'conversation_id' => '22222222-2222-4222-8222-222222222222',
            'timestamp' => new \DateTimeImmutable('2026-01-02T03:04:05.678543Z'),
        ]);
        $xpath = $this->xpath($document);

        $userId = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage')->item(0);
        $this->assertInstanceOf(DOMElement::class, $userId);

        $messageInfo = $xpath->query('eb:MessageInfo', $userId)->item(0);
        $this->assertInstanceOf(DOMElement::class, $messageInfo);

        $this->assertSame('2026-01-02T03:04:05.678Z', $xpath->query('eb:Timestamp', $messageInfo)->item(0)->nodeValue);
        $this->assertSame(
            'urn:uuid:11111111-1111-4111-8111-111111111111',
            $xpath->query('eb:MessageId', $messageInfo)->item(0)->nodeValue
        );

        $this->assertSame(
            '22222222-2222-4222-8222-222222222222',
            $xpath->query('eb:CollaborationInfo/eb:ConversationId', $userId)->item(0)->nodeValue
        );
        $this->assertSame('urn:example:service', $xpath->query('eb:CollaborationInfo/eb:Service', $userId)->item(0)->nodeValue);
        $this->assertSame('Submit', $xpath->query('eb:CollaborationInfo/eb:Action', $userId)->item(0)->nodeValue);
    }

    public function test_party_info_carries_ids_roles_and_types(): void
    {
        [$document] = $this->build();
        $xpath = $this->xpath($document);

        $from = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:PartyInfo/eb:From')->item(0);
        $this->assertInstanceOf(DOMElement::class, $from);

        $partyId = $xpath->query('eb:PartyId', $from)->item(0);
        $this->assertInstanceOf(DOMElement::class, $partyId);
        $this->assertSame('BE0123456789', $partyId->nodeValue);
        $this->assertSame(As4Constants::PARTYID_UNREGISTERED, $partyId->getAttribute('type'));
        $this->assertSame(As4Constants::ROLE_INITIATOR, $xpath->query('eb:Role', $from)->item(0)->nodeValue);

        $to = $xpath->query('eb:To', $from->parentNode)->item(0);
        $this->assertSame('BE0987654321', $xpath->query('eb:PartyId', $to)->item(0)->nodeValue);
        $this->assertSame(As4Constants::ROLE_RESPONDER, $xpath->query('eb:Role', $to)->item(0)->nodeValue);
    }

    public function test_message_properties_are_emitted(): void
    {
        [$document] = $this->build();
        $xpath = $this->xpath($document);

        $properties = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:MessageProperties/eb:Property');
        $this->assertSame(1, $properties->length);

        $property = $properties->item(0);
        $this->assertSame('businessDocument', $property->attributes->getNamedItem('name')->nodeValue);
        $this->assertSame('invoice', $property->nodeValue);
    }

    public function test_payload_info_references_cid_and_part_properties(): void
    {
        [$document, , $parts] = $this->build();
        $xpath = $this->xpath($document);

        $part = $parts[0];
        $this->assertNotNull($part->cid);
        $this->assertMatchesRegularExpression('/@ebox$/', (string) $part->cid);

        $partInfo = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:PayloadInfo/eb:PartInfo')->item(0);
        $this->assertInstanceOf(DOMElement::class, $partInfo);
        $this->assertSame('cid:'.$part->cid, $partInfo->getAttribute('href'));

        $properties = $xpath->query('eb:PartProperties/eb:Property', $partInfo);

        $found = [];
        for ($i = 0; $i < $properties->length; $i++) {
            $node = $properties->item($i);
            $found[$node->getAttribute('name')] = $node->nodeValue;
        }

        $this->assertSame('application/xml', $found['MimeType']);
        $this->assertSame('utf-8', $found['CharacterSet'] ?? null);
        $this->assertArrayNotHasKey('CompressionType', $found);
    }

    public function test_compressed_part_exposes_compression_type(): void
    {
        $pmode = $this->pmode();
        $builder = new UserMessageBuilder(new MessageIdGenerator);
        $part = (new PayloadPart('<Z/>'))
            ->withCompression(true);

        [$document] = $builder->build($pmode, [$part], []);
        $xpath = $this->xpath($document);

        $property = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:PayloadInfo/eb:PartInfo/eb:PartProperties/eb:Property[@name="CompressionType"]')->item(0);
        $this->assertNotNull($property);
        $this->assertSame(As4Constants::COMPRESSION_GZIP, $property->nodeValue);
    }

    public function test_original_sender_and_final_recipient_properties(): void
    {
        [$document] = $this->build([
            'original_sender' => ['id' => 'BE0198765432', 'type' => As4Constants::PARTYID_UNREGISTERED],
            'final_recipient' => ['id' => 'BE0123456799', 'type' => As4Constants::PARTYID_UNREGISTERED],
        ]);
        $xpath = $this->xpath($document);

        $properties = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:MessageProperties/eb:Property');

        $found = [];
        for ($i = 0; $i < $properties->length; $i++) {
            $node = $properties->item($i);
            $found[$node->getAttribute('name')] = $node->nodeValue;
        }

        $this->assertSame('BE0198765432', $found['originalSender'] ?? null);
        $this->assertSame('BE0123456799', $found['finalRecipient'] ?? null);
    }
}
