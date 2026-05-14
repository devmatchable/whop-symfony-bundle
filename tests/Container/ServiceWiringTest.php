<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Container;

use Matchable\Whop\Bundle\Tests\Kernel\TestKernel;
use Matchable\Whop\Bundle\Webhook\EventDispatchingWebhookHandler;
use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;
use Matchable\Whop\Webhook\WebhookVerifier;
use Matchable\Whop\WhopApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ServiceWiringTest extends TestCase
{
    public function testCoreServicesResolveAndAreTheExpectedTypes(): void
    {
        $kernel = new TestKernel(['api_key' => 'apik_test', 'webhook_secret' => 'ws_test']);
        $kernel->boot();

        $container = $kernel->getContainer()->get('test.service_container');

        self::assertInstanceOf(WhopApiClient::class, $container->get(WhopApiClient::class));
        self::assertInstanceOf(WebhookVerifier::class, $container->get(WebhookVerifier::class));
        self::assertInstanceOf(
            EventDispatchingWebhookHandler::class,
            $container->get(WhopWebhookHandlerInterface::class),
        );

        (new Filesystem())->remove($kernel->getCacheDir());
        $kernel->shutdown();
    }
}
