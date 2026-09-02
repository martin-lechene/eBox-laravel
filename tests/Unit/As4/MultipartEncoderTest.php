<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use Ebox\Enterprise\As4\As4Constants;
use Ebox\Enterprise\As4\As4Message;
use Ebox\Enterprise\As4\MultipartEncoder;
use Ebox\Enterprise\As4\PayloadPart;
use Ebox\Enterprise\Tests\TestCase;

class MultipartEncoderTest extends TestCase
{
    private function encoder(): MultipartEncoder
    {
        return new MultipartEncoder;
    }

    private function parseContentType(string $header): array
    {
        $params = [];
        preg_match_all('/([a-zA-Z-]+)="?([^";]+)"?/', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $params[strtolower($match[1])] = $match[2];
        }

        return $params;
    }

    public function test_content_type_is_multipart_related_soap(): void
    {
        $message = new As4Message('<soap/>', 'urn:uuid:1', 'urn:uuid:2', 'conv-1');
        $message->addPart(new PayloadPart('RAW'));

        $mime = $this->encoder()->encode($message, 'ebox-boundary-0123456789abcdef');

        $params = $this->parseContentType($mime['content_type']);
        $this->assertSame('multipart/related', explode(';', $mime['content_type'])[0]);
        $this->assertSame(As4Constants::MIME_SOAP, $params['type']);
        $this->assertSame('ebox-boundary-0123456789abcdef', $params['boundary']);
        $this->assertSame(As4Constants::MIME_SOAP, $params['start-info']);
    }

    public function test_start_matches_first_part_content_id(): void
    {
        $message = new As4Message('<soap/>', 'urn:uuid:1', null, 'conv-1');
        $message->addPart(new PayloadPart('RAW', 'application/xml', 'utf-8'));

        $mime = $this->encoder()->encode($message, 'ebox-boundary-0123456789abcdef');

        $start = $this->parseContentType($mime['content_type'])['start'] ?? null;
        $this->assertMatchesRegularExpression('/^<[^@]+@ebox>$/', (string) $start);

        $soapPartMarker = 'Content-ID: '.$start."\r\n";
        $this->assertStringContainsString($soapPartMarker, $mime['body']);
    }

    public function test_body_contains_soap_and_payload_parts_with_crlf_and_closing_boundary(): void
    {
        $message = new As4Message('<env:Envelope/>', 'urn:uuid:1', null, 'conv-1');
        $payload = new PayloadPart('PAYLOAD-BODY', 'application/xml', 'utf-8', cid: 'payload-1@ebox');
        $message->addPart($payload);

        $mime = $this->encoder()->encode($message, 'ebox-boundary-0123456789abcdef');

        $this->assertStringContainsString("\r\n", $mime['body']);
        $this->assertStringEndsWith("--ebox-boundary-0123456789abcdef--\r\n", $mime['body']);
        $this->assertStringContainsString('Content-Type: '.As4Constants::MIME_SOAP.'; charset=UTF-8', $mime['body']);
        $this->assertStringContainsString('Content-ID: <payload-1@ebox>', $mime['body']);
        $this->assertStringContainsString('PAYLOAD-BODY', $mime['body']);
    }

    public function test_compressed_payload_is_marked_application_gzip_and_roundtrips(): void
    {
        $original = str_repeat('invoice-content-', 100);
        $message = new As4Message('<env:Envelope/>', 'urn:uuid:1', null, 'conv-1');
        $payload = (new PayloadPart($original, 'application/xml', 'utf-8'))
            ->withCompression(true)
            ->withCid('gzip-1@ebox');
        $message->addPart($payload);

        $mime = $this->encoder()->encode($message, 'ebox-boundary-0123456789abcdef');

        $this->assertStringContainsString('Content-Type: application/xml; charset=utf-8', $mime['body']);
        $this->assertStringContainsString('Content-Type: application/xml; charset=utf-8', $mime['body']);
        $this->assertStringContainsString('Content-Transfer-Encoding: binary', $mime['body']);

        $startMarker = 'Content-ID: <gzip-1@ebox>'."\r\n\r\n";
        $position = strpos($mime['body'], $startMarker);
        $this->assertNotFalse($position);
        $partBody = substr($mime['body'], $position + strlen($startMarker));
        $partBody = explode("\r\n--ebox-boundary-0123456789abcdef", $partBody)[0];

        $this->assertSame($original, gzdecode($partBody));
    }

    public function test_uncompressed_text_payload_uses_8bit_transfer_encoding(): void
    {
        $message = new As4Message('<env:Envelope/>', 'urn:uuid:1', null, 'conv-1');
        $message->addPart(new PayloadPart('PLAIN', 'text/plain', 'utf-8', cid: 't-1@ebox'));

        $mime = $this->encoder()->encode($message, 'ebox-boundary-0123456789abcdef');

        $this->assertStringContainsString('Content-Transfer-Encoding: 8bit', $mime['body']);
        $this->assertStringContainsString('PLAIN', $mime['body']);
    }
}
