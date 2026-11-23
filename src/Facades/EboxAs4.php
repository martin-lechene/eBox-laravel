<?php

namespace Ebox\Enterprise\Facades;

use Ebox\Enterprise\As4\Contracts\As4ClientInterface;
use Ebox\Enterprise\As4\EboxAs4Client;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the AS4/ebMS3 push client.
 *
 * @method static mixed pmode()
 * @method static \Ebox\Enterprise\As4\As4Response send(\Ebox\Enterprise\As4\PayloadPart $payload, array $context = [])
 *
 * @see EboxAs4Client
 */
class EboxAs4 extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return As4ClientInterface::class;
    }
}
