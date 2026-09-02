<?php

namespace Ebox\Enterprise\As4;

use DateTimeInterface;

/**
 * Builds the ebMS3 UserMessage SOAP 1.2 envelope (header only, empty body).
 *
 * The resulting signature-ready document keeps references to the
 * eb:MessageInfo node (to be wrapped by MessageSigner) and to the
 * wsse:Security element.
 */
final class UserMessageBuilder
{
    public function __construct(
        private readonly MessageIdGenerator $ids,
    ) {}

    /**
     * Context keys: conversation_id, message_id, timestamp, message_properties,
     * original_sender, final_recipient.
     *
     * @param  array<int, PayloadPart>  $parts
     * @return array{0: \DOMDocument, 1: \DOMElement, 2: array<int, PayloadPart>, 3: string}
     */
    public function build(Pmode $pmode, array $parts, array $context = []): array
    {
        $messageId = (string) ($context['message_id'] ?? $this->ids->messageId());
        $conversationId = (string) ($context['conversation_id'] ?? $this->ids->conversationId());
        $timestamp = $context['timestamp'] ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;

        $envelope = $doc->createElementNS(As4Constants::SOAP12_NS, 'env:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:env', As4Constants::SOAP12_NS);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:eb', As4Constants::EBMS_NS);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsse', As4Constants::WSSE_NS);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsu', As4Constants::WSU_NS);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', As4Constants::DS_NS);
        $doc->appendChild($envelope);

        $header = $doc->createElementNS(As4Constants::SOAP12_NS, 'env:Header');
        $envelope->appendChild($header);

        $security = $doc->createElementNS(As4Constants::WSSE_NS, 'wsse:Security');
        $security->setAttributeNS(As4Constants::SOAP12_NS, 'env:mustUnderstand', 'true');
        $header->appendChild($security);

        $messaging = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:Messaging');
        $header->appendChild($messaging);

        $userMessage = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:UserMessage');
        $messaging->appendChild($userMessage);

        $messageInfo = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:MessageInfo');
        $userMessage->appendChild($messageInfo);

        $messageInfo->appendChild($doc->createElementNS(
            As4Constants::EBMS_NS,
            'eb:Timestamp',
            self::formatTimestamp($timestamp)
        ));
        $messageInfo->appendChild($doc->createElementNS(As4Constants::EBMS_NS, 'eb:MessageId', $messageId));

        $partyInfo = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:PartyInfo');
        $userMessage->appendChild($partyInfo);

        $from = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:From');
        $from->appendChild($this->partyIdElement($doc, $pmode->from));
        $from->appendChild($doc->createElementNS(As4Constants::EBMS_NS, 'eb:Role', $pmode->fromRole));
        $partyInfo->appendChild($from);

        $to = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:To');
        $to->appendChild($this->partyIdElement($doc, $pmode->to));
        $to->appendChild($doc->createElementNS(As4Constants::EBMS_NS, 'eb:Role', $pmode->toRole));
        $partyInfo->appendChild($to);

        $collaboration = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:CollaborationInfo');
        $userMessage->appendChild($collaboration);

        if ($pmode->agreementRef !== null && $pmode->agreementRef !== '') {
            $agreement = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:AgreementRef', $pmode->agreementRef);
            if ($pmode->agreementType !== null && $pmode->agreementType !== '') {
                $agreement->setAttribute('type', $pmode->agreementType);
            }
            $collaboration->appendChild($agreement);
        }

        $service = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:Service', $pmode->service);
        if ($pmode->serviceType !== null && $pmode->serviceType !== '') {
            $service->setAttribute('type', $pmode->serviceType);
        }
        $collaboration->appendChild($service);
        $collaboration->appendChild($doc->createElementNS(As4Constants::EBMS_NS, 'eb:Action', $pmode->action));
        $collaboration->appendChild($doc->createElementNS(As4Constants::EBMS_NS, 'eb:ConversationId', $conversationId));

        $this->appendMessageProperties($doc, $userMessage, $pmode, $context);

        $payloadInfo = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:PayloadInfo');
        $userMessage->appendChild($payloadInfo);

        $attached = [];
        foreach ($parts as $part) {
            $cid = $part->cid ?? $this->ids->contentId();
            $attached[] = $part->withCid($cid);

            $partInfo = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:PartInfo');
            $partInfo->setAttribute('href', 'cid:'.$cid);
            $payloadInfo->appendChild($partInfo);

            $properties = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:PartProperties');
            $properties->appendChild($this->propertyElement($doc, 'MimeType', $part->mimeType));
            if ($part->characterSet !== null && $part->characterSet !== '') {
                $properties->appendChild($this->propertyElement($doc, 'CharacterSet', $part->characterSet));
            }
            if ($part->compressed) {
                $properties->appendChild($this->propertyElement($doc, 'CompressionType', As4Constants::COMPRESSION_GZIP));
            }
            $partInfo->appendChild($properties);
            $payloadInfo->appendChild($partInfo);
        }

        $body = $doc->createElementNS(As4Constants::SOAP12_NS, 'env:Body');
        $envelope->appendChild($body);

        return [$doc, $security, $attached, $messageId];
    }

    private function partyIdElement(\DOMDocument $doc, array $party): \DOMElement
    {
        $element = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:PartyId', (string) $party['id']);
        $element->setAttribute('type', (string) ($party['type'] ?? As4Constants::PARTYID_UNREGISTERED));

        return $element;
    }

    private function propertyElement(\DOMDocument $doc, string $name, string $value): \DOMElement
    {
        $element = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:Property', $value);
        $element->setAttribute('name', $name);

        return $element;
    }

    private function appendMessageProperties(\DOMDocument $doc, \DOMElement $userMessage, Pmode $pmode, array $context): void
    {
        $properties = [];
        foreach ($pmode->messageProperties as $property) {
            $properties[] = $property;
        }

        if (! empty($context['message_properties']) && is_array($context['message_properties'])) {
            foreach ($context['message_properties'] as $property) {
                $properties[] = $property;
            }
        }

        $originalSender = $context['original_sender'] ?? null;
        if ($originalSender !== null && is_array($originalSender)) {
            $properties[] = ['name' => 'originalSender', 'value' => $originalSender['id'], 'type' => $originalSender['type'] ?? null];
        }

        $finalRecipient = $context['final_recipient'] ?? null;
        if ($finalRecipient !== null && is_array($finalRecipient)) {
            $properties[] = ['name' => 'finalRecipient', 'value' => $finalRecipient['id'], 'type' => $finalRecipient['type'] ?? null];
        }

        if ($properties === []) {
            return;
        }

        $container = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:MessageProperties');
        $userMessage->appendChild($container);

        foreach ($properties as $property) {
            $name = (string) ($property['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $element = $doc->createElementNS(As4Constants::EBMS_NS, 'eb:Property', (string) ($property['value'] ?? ''));
            $element->setAttribute('name', $name);
            if (! empty($property['type'])) {
                $element->setAttribute('type', (string) $property['type']);
            }
            $container->appendChild($element);
        }
    }

    public static function formatTimestamp(DateTimeInterface $date): string
    {
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format('Y-m-d\TH:i:s').sprintf('.%03dZ', (int) $date->format('v'));
    }
}
