<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Fixtures;

use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;

final class RecordingWebhookHandler implements WhopWebhookHandlerInterface
{
    /** @var list<array{payload: array<string, mixed>, rawPayload: string}> */
    public array $received = [];

    public function handle(array $payload, string $rawPayload): void
    {
        $this->received[] = ['payload' => $payload, 'rawPayload' => $rawPayload];
    }
}
