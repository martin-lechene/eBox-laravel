<?php

namespace Ebox\Enterprise\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Ebox\Enterprise\Events\MessageSent;
use Ebox\Enterprise\Events\MessageDelivered;
use Ebox\Enterprise\Events\MessageRead;
use Ebox\Enterprise\Listeners\LogMessageActivity;
use Ebox\Enterprise\Listeners\NotifyMessageStatus;
use Ebox\Enterprise\Listeners\UpdateMessageTimeline;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            LogMessageActivity::class . '@handle',
            UpdateMessageTimeline::class . '@handleSent',
        ],
        MessageDelivered::class => [
            LogMessageActivity::class . '@handleDelivered',
            NotifyMessageStatus::class . '@handleDelivered',
            UpdateMessageTimeline::class . '@handleDelivered',
        ],
        MessageRead::class => [
            LogMessageActivity::class . '@handleRead',
            NotifyMessageStatus::class . '@handleRead',
            UpdateMessageTimeline::class . '@handleRead',
        ],
    ];
    
    public function boot(): void
    {
        parent::boot();
    }
}

