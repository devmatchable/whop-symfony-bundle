<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Webhook;

use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Default webhook handler — dispatches a {@see WhopWebhookReceivedEvent} so
 * applications can react with an ordinary event listener.
 */
final readonly class EventDispatchingWebhookHandler implements WhopWebhookHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(array $payload, string $rawPayload): void
    {
        $this->eventDispatcher->dispatch(new WhopWebhookReceivedEvent($rawPayload, $payload));
    }
}
