<?php

declare(strict_types=1);

namespace App\EventListener;

use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Starter listener for incoming Whop webhooks. The bundle has already verified
 * the signature before this runs. Map $event->payload to your own domain type
 * and dispatch your application logic from here.
 */
#[AsEventListener]
final class WhopWebhookListener
{
    public function __invoke(WhopWebhookReceivedEvent $event): void
    {
        // $event->payload — decoded webhook JSON (array<string, mixed>)
        // $event->rawPayload — verified raw request body
    }
}
