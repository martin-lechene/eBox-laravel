<?php

namespace Ebox\Enterprise\Facades;

use Ebox\Enterprise\Core\Contracts\MessagingInterface;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;
use Ebox\Enterprise\Models\EboxMessage;
use Illuminate\Support\Facades\Facade;

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
