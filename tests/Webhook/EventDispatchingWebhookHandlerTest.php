<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Webhook;

use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use Matchable\Whop\Bundle\Webhook\EventDispatchingWebhookHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventDispatchingWebhookHandlerTest extends TestCase
{
    public function testItDispatchesAWebhookReceivedEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $received = null;
        $dispatcher->addListener(
            WhopWebhookReceivedEvent::class,
            static function (WhopWebhookReceivedEvent $event) use (&$received): void {
                $received = $event;
            },
        );

        $handler = new EventDispatchingWebhookHandler($dispatcher);
        $handler->handle(['event' => 'payment.created'], '{"event":"payment.created"}');

        self::assertInstanceOf(WhopWebhookReceivedEvent::class, $received);
        self::assertSame(['event' => 'payment.created'], $received->payload);
        self::assertSame('{"event":"payment.created"}', $received->rawPayload);
    }
}
