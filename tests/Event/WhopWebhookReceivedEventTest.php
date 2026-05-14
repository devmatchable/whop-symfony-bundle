<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Event;

use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use PHPUnit\Framework\TestCase;

final class WhopWebhookReceivedEventTest extends TestCase
{
    public function testItExposesRawAndDecodedPayload(): void
    {
        $event = new WhopWebhookReceivedEvent(
            rawPayload: '{"event":"payment.created"}',
            payload: ['event' => 'payment.created'],
        );

        self::assertSame('{"event":"payment.created"}', $event->rawPayload);
        self::assertSame(['event' => 'payment.created'], $event->payload);
    }
}
