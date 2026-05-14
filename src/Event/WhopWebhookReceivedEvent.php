<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched by the default webhook handler after a webhook signature has
 * been verified. Apps subscribe to this to react to incoming Whop webhooks.
 */
final class WhopWebhookReceivedEvent extends Event
{
    /**
     * @param string               $rawPayload the verified raw request body
     * @param array<string, mixed> $payload    the decoded webhook JSON
     */
    public function __construct(
        public readonly string $rawPayload,
        public readonly array $payload,
    ) {
    }
}
