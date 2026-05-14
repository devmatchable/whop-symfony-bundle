<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Webhook;

/**
 * Contract for handling a verified incoming Whop webhook.
 *
 * The bundle registers {@see EventDispatchingWebhookHandler} as the default
 * implementation. Override it by registering your own service and aliasing
 * this interface to it, by extending the default class, or with #[AsDecorator].
 */
interface WhopWebhookHandlerInterface
{
    /**
     * @param array<string, mixed> $payload    the decoded webhook JSON
     * @param string               $rawPayload the verified raw request body
     */
    public function handle(array $payload, string $rawPayload): void;
}
