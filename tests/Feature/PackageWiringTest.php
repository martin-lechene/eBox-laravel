<?php

namespace Ebox\Enterprise\Tests\Feature;

use Ebox\Enterprise\As4\Contracts\As4ClientInterface;
use Ebox\Enterprise\Facades\EboxAs4;
use Ebox\Enterprise\Tests\TestCase;

class PackageWiringTest extends TestCase
{
    public function test_routes_are_registered_under_the_configured_prefix(): void
    {
        $this->assertSame('/api/ebox/v1/messages', route('ebox.messages.send', [], false));
        $this->assertSame('/api/ebox/v1/health', route('ebox.health', [], false));
    }

    public function test_as4_facade_resolves_the_client_contract(): void
    {
        $this->assertInstanceOf(As4ClientInterface::class, EboxAs4::getFacadeRoot());
    }
}
