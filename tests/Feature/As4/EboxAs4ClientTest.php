<?php

namespace Ebox\Enterprise\Tests\Feature\As4;

use Ebox\Enterprise\As4\As4Exception;
use Ebox\Enterprise\As4\As4ResponseType;
use Ebox\Enterprise\As4\Contracts\As4ClientInterface;
use Ebox\Enterprise\As4\EboxAs4Client;
use Ebox\Enterprise\As4\PayloadPart;
use Ebox\Enterprise\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class EboxAs4ClientTest extends TestCase
{
    private function config(array $overrides = []): array
    {
        return array_merge([
            'enabled' => true,
            'endpoint' => 'https://msh.example.test/as4',
            'timeout' => 5,
            'client_cert' => $this->fixturesPath('certs/sender.crt'),
            'client_key' => $this->fixturesPath('certs/sender.key'),
            'compression_type' => 'application/gzip',
            'from' => ['id' => 'BE0123456789'],
            'to' => ['id' => 'BE0987654321'],
        ], $overrides);
    }

    /**
     * @param  array<int, ResponseInterface|RequestException>  $responses
     */
    private function httpClient(array $responses, ?array &$requests): Client
    {
        $requests = [];
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($requests));

        return new Client(['handler' => $handler]);
    }

    private function receiptResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/soap+xml; charset=UTF-8'], <<<'XML'
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
              xmlns:eb="http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/">
  <env:Header>
    <eb:Messaging>
      <eb:SignalMessage>
        <eb:MessageInfo>
          <eb:Timestamp>2026-01-01T10:00:00.100Z</eb:Timestamp>
          <eb:MessageId>urn:uuid:receipt-1</eb:MessageId>
          <eb:RefToMessageId>urn:uuid:request-1</eb:RefToMessageId>
        </eb:MessageInfo>
        <eb:Receipt>
          <eb:NonRepudiationInformation>
            <eb:MessageHash digestMethod="http://www.w3.org/2001/04/xmlenc#sha256">cmVjZWlwdA==</eb:MessageHash>
          </eb:NonRepudiationInformation>
        </eb:Receipt>
      </eb:SignalMessage>
    </eb:Messaging>
  </env:Header>
  <env:Body xsi:nil="true"/>
</env:Envelope>
XML);
    }

    private function errorResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/soap+xml'], <<<'XML'
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
              xmlns:eb="http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/">
  <env:Header>
    <eb:Messaging>
      <eb:SignalMessage>
        <eb:MessageInfo>
          <eb:Timestamp>2026-01-01T10:00:00.100Z</eb:Timestamp>
          <eb:MessageId>urn:uuid:error-1</eb:MessageId>
          <eb:RefToMessageId>urn:uuid:request-1</eb:RefToMessageId>
        </eb:MessageInfo>
        <eb:ErrorList>
          <eb:Error errorCode="EBMS:0103" severity="Failure" category="Receiver">
            <eb:Description xml:lang="en">Receiver unknown</eb:Description>
          </eb:Error>
        </eb:ErrorList>
      </eb:SignalMessage>
    </eb:Messaging>
  </env:Header>
  <env:Body xsi:nil="true"/>
</env:Envelope>
XML);
    }

    public function test_pushes_a_signed_multipart_message_and_reads_the_receipt(): void
    {
        $http = $this->httpClient([$this->receiptResponse()], $requests);
        $client = EboxAs4Client::fromConfig($this->config(), $http);

        $reply = $client->send(
            new PayloadPart('<Invoice/>', 'application/xml', 'utf-8'),
            ['message_id' => 'urn:uuid:aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']
        );

        $this->assertSame(As4ResponseType::RECEIPT, $reply->type);
        $this->assertSame('urn:uuid:receipt-1', $reply->messageId);
        $this->assertSame('urn:uuid:request-1', $reply->refToMessageId);
        $this->assertSame('cmVjZWlwdA==', $reply->receiptHash);

        $this->assertCount(1, $requests);
        $request = $requests[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('msh.example.test', $request->getUri()->getHost());
        $this->assertSame('/as4', $request->getUri()->getPath());

        $contentType = $request->getHeaderLine('content-type');
        $this->assertStringContainsString('multipart/related', $contentType);
        $this->assertStringContainsString('type="application/soap+xml"', $contentType);

        $body = (string) $request->getBody();
        $this->assertStringContainsString('urn:uuid:aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $body);
        $this->assertStringContainsString('<eb:PartInfo href="cid:', $body);
        $this->assertStringContainsString('<eb:Property name="CompressionType">application/gzip</eb:Property>', $body);
        $this->assertStringContainsString('-BEGIN CERTIFICATE-', $this->senderCertificate());

        $this->verifyAs4Signature($this->extractSoapPart($body, $contentType));
    }

    private function extractSoapPart(string $body, string $contentType): string
    {
        preg_match('/boundary="([^"]+)"/', $contentType, $matches);
        $boundary = $matches[1];

        $parts = explode('--'.$boundary, $body);
        $soapPart = $parts[1] ?? '';
        $soapPart = preg_replace('/^.*?(\r?\n){2}/s', '', $soapPart);

        return trim($soapPart);
    }

    public function test_handles_an_ebms_error_signal(): void
    {
        $http = $this->httpClient([$this->errorResponse()], $requests);
        $client = EboxAs4Client::fromConfig($this->config(), $http);

        $reply = $client->send(new PayloadPart('<Invoice/>'));

        $this->assertSame(As4ResponseType::ERROR, $reply->type);
        $this->assertCount(1, $reply->errors);
        $this->assertSame('EBMS:0103', $reply->errors[0]->code);
        $this->assertSame('Receiver unknown', $reply->errors[0]->description);
    }

    public function test_transport_failure_throws_as4_exception(): void
    {
        $http = $this->httpClient([
            new ConnectException('Connection refused', new Request('POST', 'https://msh.example.test/as4')),
        ], $requests);
        $client = EboxAs4Client::fromConfig($this->config(), $http);

        $this->expectException(As4Exception::class);
        $client->send(new PayloadPart('<Invoice/>'));
    }

    public function test_missing_endpoint_throws(): void
    {
        $client = EboxAs4Client::fromConfig($this->config(['endpoint' => '']), new Client(['handler' => HandlerStack::create()]));

        $this->expectException(As4Exception::class);
        $this->expectExceptionMessageMatches('/endpoint/i');
        $client->send(new PayloadPart('<Invoice/>'));
    }

    public function test_disabled_client_throws(): void
    {
        $client = EboxAs4Client::fromConfig($this->config(['enabled' => false]), new Client(['handler' => HandlerStack::create()]));

        $this->expectException(As4Exception::class);
        $this->expectExceptionMessageMatches('/disabled/i');
        $client->send(new PayloadPart('<Invoice/>'));
    }

    public function test_is_bound_by_the_service_provider(): void
    {
        $client = $this->app->make(As4ClientInterface::class);

        $this->assertInstanceOf(EboxAs4Client::class, $client);
    }

    public function test_uncompressed_payload_omits_compression_property(): void
    {
        $http = $this->httpClient([$this->receiptResponse()], $requests);
        $client = EboxAs4Client::fromConfig($this->config(['compression_type' => 'none']), $http);

        $client->send(new PayloadPart('<Invoice/>'), ['message_id' => 'urn:uuid:request-2']);

        $body = (string) $requests[0]['request']->getBody();
        $this->assertStringNotContainsString('CompressionType', $body);
    }
}
