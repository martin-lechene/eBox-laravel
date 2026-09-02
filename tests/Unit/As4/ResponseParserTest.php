<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use Ebox\Enterprise\As4\As4Exception;
use Ebox\Enterprise\As4\As4ResponseType;
use Ebox\Enterprise\As4\ResponseParser;
use Ebox\Enterprise\Tests\TestCase;

class ResponseParserTest extends TestCase
{
    private function parser(): ResponseParser
    {
        return new ResponseParser;
    }

    public function test_parses_receipt_with_message_hash(): void
    {
        $receipt = <<<'XML'
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
              xmlns:eb="http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/">
  <env:Header>
    <eb:Messaging>
      <eb:SignalMessage>
        <eb:MessageInfo>
          <eb:Timestamp>2026-01-01T10:00:00.100Z</eb:Timestamp>
          <eb:MessageId>urn:uuid:11111111-1111-4111-8111-111111111111</eb:MessageId>
          <eb:RefToMessageId>urn:uuid:22222222-2222-4222-8222-222222222222</eb:RefToMessageId>
        </eb:MessageInfo>
        <eb:Receipt>
          <eb:NonRepudiationInformation>
            <eb:MessageHash digestMethod="http://www.w3.org/2001/04/xmlenc#sha256">aGFzaA==</eb:MessageHash>
          </eb:NonRepudiationInformation>
        </eb:Receipt>
      </eb:SignalMessage>
    </eb:Messaging>
  </env:Header>
  <env:Body xsi:nil="true"/>
</env:Envelope>
XML;

        $response = $this->parser()->parse(200, $receipt, 'application/soap+xml; charset=UTF-8');

        $this->assertSame(As4ResponseType::RECEIPT, $response->type);
        $this->assertTrue($response->isSuccess());
        $this->assertSame('urn:uuid:11111111-1111-4111-8111-111111111111', $response->messageId);
        $this->assertSame('urn:uuid:22222222-2222-4222-8222-222222222222', $response->refToMessageId);
        $this->assertSame('aGFzaA==', $response->receiptHash);
        $this->assertSame('http://www.w3.org/2001/04/xmlenc#sha256', $response->digestMethod);
    }

    public function test_parses_ebms_error_list(): void
    {
        $error = <<<'XML'
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
              xmlns:eb="http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/">
  <env:Header>
    <eb:Messaging>
      <eb:SignalMessage>
        <eb:MessageInfo>
          <eb:Timestamp>2026-01-01T10:00:00.100Z</eb:Timestamp>
          <eb:MessageId>urn:uuid:11111111-1111-4111-8111-111111111111</eb:MessageId>
          <eb:RefToMessageId>urn:uuid:22222222-2222-4222-8222-222222222222</eb:RefToMessageId>
        </eb:MessageInfo>
        <eb:ErrorList>
          <eb:Error errorCode="EBMS:0103" severity="Failure" category="Receiver"
                    refToMessageInError="urn:uuid:33333333-3333-4333-8333-333333333333">
            <eb:Description xml:lang="en">Receiver unknown</eb:Description>
            <eb:ErrorDetail>BE0000000000 is not a known party</eb:ErrorDetail>
          </eb:Error>
        </eb:ErrorList>
      </eb:SignalMessage>
    </eb:Messaging>
  </env:Header>
  <env:Body xsi:nil="true"/>
</env:Envelope>
XML;

        $response = $this->parser()->parse(200, $error);

        $this->assertSame(As4ResponseType::ERROR, $response->type);
        $this->assertTrue($response->isError());
        $this->assertCount(1, $response->errors);

        $as4Error = $response->errors[0];
        $this->assertSame('EBMS:0103', $as4Error->code);
        $this->assertSame('Failure', $as4Error->severity);
        $this->assertSame('Receiver', $as4Error->category);
        $this->assertSame('urn:uuid:33333333-3333-4333-8333-333333333333', $as4Error->refToMessageInError);
        $this->assertSame('Receiver unknown', $as4Error->description);
        $this->assertSame('BE0000000000 is not a known party', $as4Error->errorDetail);
        $this->assertSame('Receiver unknown', $response->description);
    }

    public function test_parses_soap_12_fault(): void
    {
        $fault = <<<'XML'
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
  <env:Body>
    <env:Fault>
      <env:Code>
        <env:Value>env:Sender</env:Value>
      </env:Code>
      <env:Reason>
        <env:Text xml:lang="en">Invalid header</env:Text>
      </env:Reason>
    </env:Fault>
  </env:Body>
</env:Envelope>
XML;

        $response = $this->parser()->parse(500, $fault);

        $this->assertSame(As4ResponseType::FAULT, $response->type);
        $this->assertTrue($response->isError());
        $this->assertSame('env:Sender', $response->faultCode);
        $this->assertSame('Invalid header', $response->description);
    }

    public function test_parses_soap_11_fault(): void
    {
        $fault = <<<'XML'
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <soap:Fault>
      <faultcode>soap:Server</faultcode>
      <faultstring>Processing error</faultstring>
    </soap:Fault>
  </soap:Body>
</soap:Envelope>
XML;

        $response = $this->parser()->parse(500, $fault);

        $this->assertSame(As4ResponseType::FAULT, $response->type);
        $this->assertSame('soap:Server', $response->faultCode);
        $this->assertSame('Processing error', $response->description);
    }

    public function test_empty_200_is_success_without_signal(): void
    {
        $response = $this->parser()->parse(200, '');

        $this->assertSame(As4ResponseType::NONE, $response->type);
        $this->assertTrue($response->isSuccess());
    }

    public function test_non_xml_error_response_throws(): void
    {
        $this->expectException(As4Exception::class);

        $this->parser()->parse(500, '<html><body>Gateway Timeout</body></html>');
    }
}
