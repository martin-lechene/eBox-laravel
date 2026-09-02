<?php

namespace Ebox\Enterprise\Tests\Unit\As4;

use Ebox\Enterprise\As4\As4Constants;
use Ebox\Enterprise\As4\Pmode;
use Ebox\Enterprise\Tests\TestCase;

class PmodeTest extends TestCase
{
    public function test_from_config_applies_defaults(): void
    {
        $pmode = Pmode::fromConfig([
            'from' => ['id' => 'BE0123456789', 'type' => 'urn:oasis:names:tc:ebcore:partyid-type:unregistered'],
            'to' => ['id' => 'BE0987654321', 'type' => 'urn:oasis:names:tc:ebcore:partyid-type:unregistered'],
        ]);

        $this->assertSame('ebox-as4-push', $pmode->id);
        $this->assertSame(As4Constants::ROLE_INITIATOR, $pmode->fromRole);
        $this->assertSame(As4Constants::ROLE_RESPONDER, $pmode->toRole);
        $this->assertSame(As4Constants::SERVICE_DEFAULT, $pmode->service);
        $this->assertSame(As4Constants::ACTION_DEFAULT, $pmode->action);
        $this->assertTrue($pmode->usesCompression());
    }

    public function test_from_config_keeps_custom_values(): void
    {
        $pmode = Pmode::fromConfig([
            'pmode_id' => 'peppol-push-01',
            'from' => ['id' => 'FROM', 'type' => 'urn:oasis:names:tc:ebcore:partyid-type:unregistered'],
            'to' => ['id' => 'TO', 'type' => 'urn:oasis:names:tc:ebcore:partyid-type:unregistered'],
            'service' => 'urn:example:svc',
            'action' => 'Submit',
            'agreement_ref' => 'agr-1',
            'compression_type' => 'none',
        ]);

        $this->assertSame('peppol-push-01', $pmode->id);
        $this->assertSame('FROM', $pmode->fromPartyId());
        $this->assertSame('TO', $pmode->toPartyId());
        $this->assertSame('urn:example:svc', $pmode->service);
        $this->assertSame('Submit', $pmode->action);
        $this->assertSame('agr-1', $pmode->agreementRef);
        $this->assertFalse($pmode->usesCompression());
        $this->assertNull($pmode->compressionType);
    }

    public function test_gzip_compression_requested_by_default(): void
    {
        $pmode = Pmode::fromConfig([
            'from' => ['id' => 'A'],
            'to' => ['id' => 'B'],
        ]);

        $this->assertSame(As4Constants::COMPRESSION_GZIP, $pmode->compressionType);
    }
}
