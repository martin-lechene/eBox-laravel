<?php

namespace Ebox\Enterprise\As4;

/**
 * Parses the MSH reply: an eb3:SignalMessage (Receipt or ErrorList) or a
 * SOAP Fault. Robust to both SOAP 1.1 and 1.2 naming.
 */
final class ResponseParser
{
    public function parse(int $status, string $body, ?string $contentType = null): As4Response
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return ($status >= 200 && $status < 300)
                ? new As4Response(type: As4ResponseType::NONE, rawBody: $body)
                : new As4Response(type: As4ResponseType::FAULT, description: "Empty response (HTTP $status)", rawBody: $body);
        }

        $doc = new \DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $loaded = $doc->loadXML($trimmed);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (! $loaded) {
            if ($status >= 200 && $status < 300) {
                return new As4Response(type: As4ResponseType::NONE, rawBody: $body);
            }

            throw new As4Exception("AS4 endpoint returned non-XML response (HTTP $status)");
        }

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('eb', As4Constants::EBMS_NS);
        $xpath->registerNamespace('soap12', As4Constants::SOAP12_NS);
        $xpath->registerNamespace('soap11', As4Constants::SOAP11_NS);

        $signal = $xpath->query('//eb:SignalMessage')->item(0);
        if ($signal instanceof \DOMElement) {
            return $this->parseSignalMessage($signal, $trimmed);
        }

        $fault = $xpath->query('//soap12:Fault | //soap11:Fault')->item(0);
        if ($fault instanceof \DOMElement) {
            return $this->parseFault($fault, $trimmed);
        }

        if ($status >= 400) {
            throw new As4Exception("AS4 endpoint returned HTTP $status without a parseable SOAP response");
        }

        return new As4Response(type: As4ResponseType::NONE, rawBody: $body);
    }

    private function parseSignalMessage(\DOMElement $signal, string $body): As4Response
    {
        $xpath = new \DOMXPath($signal->ownerDocument);
        $xpath->registerNamespace('eb', As4Constants::EBMS_NS);

        $messageId = $this->text($xpath, 'eb:MessageInfo/eb:MessageId', $signal);
        $refTo = $this->text($xpath, 'eb:MessageInfo/eb:RefToMessageId', $signal);

        $receipt = $xpath->query('eb:Receipt', $signal)->item(0);
        if ($receipt instanceof \DOMElement) {
            return new As4Response(
                type: As4ResponseType::RECEIPT,
                messageId: $messageId,
                refToMessageId: $refTo,
                receiptHash: $this->text($xpath, 'eb:Receipt/eb:NonRepudiationInformation/eb:MessageHash', $signal),
                digestMethod: $this->attribute($xpath, 'eb:Receipt/eb:NonRepudiationInformation/eb:MessageHash', 'digestMethod', $signal),
                rawBody: $body,
            );
        }

        $errors = [];
        $errorNodes = $xpath->query('eb:ErrorList/eb:Error', $signal);
        foreach ($errorNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $errors[] = new As4Error(
                code: (string) ($node->getAttribute('errorCode') ?: '(empty)'),
                severity: $node->hasAttribute('severity') ? (string) $node->getAttribute('severity') : null,
                category: $node->hasAttribute('category') ? (string) $node->getAttribute('category') : null,
                refToMessageInError: $node->hasAttribute('refToMessageInError') ? (string) $node->getAttribute('refToMessageInError') : null,
                description: $this->text($xpath, 'eb:Description', $node),
                errorDetail: $this->text($xpath, 'eb:ErrorDetail', $node),
            );
        }

        return new As4Response(
            type: As4ResponseType::ERROR,
            messageId: $messageId,
            refToMessageId: $refTo,
            errors: $errors,
            description: $errors[0]->description ?? null,
            rawBody: $body,
        );
    }

    private function parseFault(\DOMElement $fault, string $body): As4Response
    {
        $xpath = new \DOMXPath($fault->ownerDocument);

        $value = $xpath->query('*[local-name() = "Code"]/*[local-name() = "Value"]', $fault)->item(0);
        $code = $value instanceof \DOMNode ? $value->nodeValue : $this->text($xpath, '*[local-name() = "faultcode"]', $fault);

        $text = $xpath->query('*[local-name() = "Reason"]/*[local-name() = "Text"]', $fault)->item(0);
        $description = $text instanceof \DOMNode ? $text->nodeValue : $this->text($xpath, '*[local-name() = "faultstring"]', $fault);

        return new As4Response(
            type: As4ResponseType::FAULT,
            faultCode: $code,
            description: $description,
            rawBody: $body,
        );
    }

    private function text(\DOMXPath $xpath, string $query, \DOMNode $context): ?string
    {
        $node = $xpath->query($query, $context)->item(0);

        return $node instanceof \DOMNode ? $node->nodeValue : null;
    }

    private function attribute(\DOMXPath $xpath, string $query, string $name, \DOMNode $context): ?string
    {
        $node = $xpath->query($query, $context)->item(0);

        return $node instanceof \DOMElement && $node->hasAttribute($name) ? $node->getAttribute($name) : null;
    }
}
