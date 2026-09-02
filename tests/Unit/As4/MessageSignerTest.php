<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use DOMElement;
use DOMXPath;
use Ebox\Enterprise\As4\As4Constants;
use Ebox\Enterprise\As4\MessageIdGenerator;
use Ebox\Enterprise\As4\MessageSigner;
use Ebox\Enterprise\As4\PayloadPart;
use Ebox\Enterprise\As4\Pmode;
use Ebox\Enterprise\As4\UserMessageBuilder;
use Ebox\Enterprise\Tests\TestCase;

class MessageSignerTest extends TestCase
{
    private function signedDocument(): \DOMDocument
    {
        [$document, $security] = $this->unsignedEnvelope();

        $messageInfo = $document->getElementsByTagNameNS(As4Constants::EBMS_NS, 'MessageInfo')->item(0);
        $this->assertInstanceOf(DOMElement::class, $messageInfo);

        $signer = new MessageSigner;
        $signer->sign(
            $document,
            $messageInfo,
            $security,
            $this->senderCertificate(),
            $this->senderPrivateKey()
        );

        return $document;
    }

    private function unsignedEnvelope(): array
    {
        $pmode = Pmode::fromConfig([
            'from' => ['id' => 'BE0123456789'],
            'to' => ['id' => 'BE0987654321'],
        ]);
        $builder = new UserMessageBuilder(new MessageIdGenerator);

        return $builder->build($pmode, [new PayloadPart('<Invoice/>')], [
            'message_id' => 'urn:uuid:33333333-3333-4333-8333-333333333333',
        ]);
    }

    private function xpath(\DOMDocument $document): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('env', As4Constants::SOAP12_NS);
        $xpath->registerNamespace('eb', As4Constants::EBMS_NS);
        $xpath->registerNamespace('wsse', As4Constants::WSSE_NS);
        $xpath->registerNamespace('wsu', As4Constants::WSU_NS);
        $xpath->registerNamespace('ds', As4Constants::DS_NS);

        return $xpath;
    }

    public function test_security_header_is_must_understand(): void
    {
        $document = $this->signedDocument();
        $xpath = $this->xpath($document);

        $security = $xpath->query('/env:Envelope/env:Header/wsse:Security')->item(0);
        $this->assertInstanceOf(DOMElement::class, $security);
        $this->assertSame('true', $security->getAttributeNS(As4Constants::SOAP12_NS, 'mustUnderstand'));
    }

    public function test_binary_security_token_is_x509_v3_base64(): void
    {
        $document = $this->signedDocument();
        $xpath = $this->xpath($document);

        $token = $xpath->query('/env:Envelope/env:Header/wsse:Security/wsse:BinarySecurityToken')->item(0);
        $this->assertInstanceOf(DOMElement::class, $token);
        $this->assertSame(As4Constants::X509V3, $token->getAttribute('ValueType'));
        $this->assertSame(As4Constants::BASE64_BINARY, $token->getAttribute('EncodingType'));
        $this->assertNotNull($token->getAttributeNS(As4Constants::WSU_NS, 'Id'));
        $this->assertNotEmpty($token->textContent);
    }

    public function test_signature_references_message_info_by_wsu_id(): void
    {
        $document = $this->signedDocument();
        $xpath = $this->xpath($document);

        $messageInfo = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:MessageInfo')->item(0);
        $this->assertInstanceOf(DOMElement::class, $messageInfo);
        $messageInfoId = $messageInfo->getAttributeNS(As4Constants::WSU_NS, 'Id');
        $this->assertNotEmpty($messageInfoId);

        $reference = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:SignedInfo/ds:Reference')->item(0);
        $this->assertInstanceOf(DOMElement::class, $reference);
        $this->assertSame('#'.$messageInfoId, $reference->getAttribute('URI'));

        $signatureMethod = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:SignedInfo/ds:SignatureMethod')->item(0);
        $this->assertSame(As4Constants::RSA_SHA256, $signatureMethod->getAttribute('Algorithm'));

        $digestMethod = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestMethod')->item(0);
        $this->assertSame(As4Constants::SHA256, $digestMethod->getAttribute('Algorithm'));

        $canonicalization = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:SignedInfo/ds:CanonicalizationMethod')->item(0);
        $this->assertSame(As4Constants::EXC_C14N, $canonicalization->getAttribute('Algorithm'));

        $transform = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:SignedInfo/ds:Reference/ds:Transforms/ds:Transform')->item(0);
        $this->assertSame(As4Constants::EXC_C14N, $transform->getAttribute('Algorithm'));
    }

    public function test_key_info_references_the_binary_security_token(): void
    {
        $document = $this->signedDocument();
        $xpath = $this->xpath($document);

        $token = $xpath->query('/env:Envelope/env:Header/wsse:Security/wsse:BinarySecurityToken')->item(0);
        $tokenId = $token->getAttributeNS(As4Constants::WSU_NS, 'Id');

        $strRef = $xpath->query('/env:Envelope/env:Header/wsse:Security/ds:Signature/ds:KeyInfo/wsse:SecurityTokenReference/wsse:Reference')->item(0);
        $this->assertInstanceOf(DOMElement::class, $strRef);
        $this->assertSame('#'.$tokenId, $strRef->getAttribute('URI'));
        $this->assertSame(As4Constants::X509V3, $strRef->getAttribute('ValueType'));
    }

    public function test_signature_verifies_against_the_sender_certificate(): void
    {
        $document = $this->signedDocument();

        $validated = $this->verifyAs4Signature($document->saveXML());

        $this->assertNotEmpty($validated);
        $first = reset($validated);
        $this->assertSame(As4Constants::EBMS_NS, $first->namespaceURI);
        $this->assertSame('MessageInfo', $first->localName);
    }

    public function test_tampering_with_message_info_invalidates_the_signature(): void
    {
        $document = $this->signedDocument();
        $xpath = $this->xpath($document);

        $messageId = $xpath->query('/env:Envelope/env:Header/eb:Messaging/eb:UserMessage/eb:MessageInfo/eb:MessageId')->item(0);
        $messageId->nodeValue = 'urn:uuid:99999999-9999-4999-8999-999999999999';

        $this->expectException(\Exception::class);
        $this->verifyAs4Signature($document->saveXML());
    }

    public function test_unsigned_envelope_has_no_signature(): void
    {
        [$document] = $this->unsignedEnvelope();
        $xpath = $this->xpath($document);

        $this->assertSame(0, $xpath->query('//ds:Signature')->length);
    }
}
