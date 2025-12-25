<?php

namespace Ebox\Enterprise\Facades;

use Illuminate\Support\Facades\Facade;
use Ebox\Enterprise\Core\Contracts\MessagingInterface;
use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;

/**
 * Facade for e-Box service
 *
 * @method static EboxMessage sendMessage(array $messageData, IntegrationProfile $profile)
 * @method static array getMessageStatus(string $messageId)
 */
class Ebox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MessagingInterface::class;
    }
}

