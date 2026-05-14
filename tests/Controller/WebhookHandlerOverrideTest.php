<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Controller;

use Matchable\Whop\Bundle\Tests\Fixtures\RecordingWebhookHandler;
use Matchable\Whop\Bundle\Tests\Kernel\TestKernel;
use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebhookHandlerOverrideTest extends TestCase
{
    private const string SECRET = 'ws_override_secret';

    public function testACustomHandlerReplacesTheDefault(): void
    {
        $extraConfig = static function (ContainerConfigurator $container): void {
            $services = $container->services();
            $services->set(RecordingWebhookHandler::class)->public();
            $services->alias(WhopWebhookHandlerInterface::class, RecordingWebhookHandler::class)
                ->public();
        };

        $kernel = new TestKernel(
            ['api_key' => 'apik_test', 'webhook_secret' => self::SECRET],
            $extraConfig,
        );
        $kernel->boot();

        $response = $kernel->handle(self::signedRequest('{"event":"payment.created"}'));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $handler = $kernel->getContainer()->get(WhopWebhookHandlerInterface::class);
        self::assertInstanceOf(RecordingWebhookHandler::class, $handler);
        self::assertCount(1, $handler->received);
        self::assertSame(['event' => 'payment.created'], $handler->received[0]['payload']);

        $dir = $kernel->getCacheDir();
        $kernel->shutdown();
        (new Filesystem())->remove($dir);
    }

    private static function signedRequest(string $payload): Request
    {
        $webhookId = 'msg_'.bin2hex(random_bytes(8));
        $timestamp = (string) time();
        $signedContent = sprintf('%s.%s.%s', $webhookId, $timestamp, $payload);
        $signature = base64_encode(hash_hmac('sha256', $signedContent, self::SECRET, true));

        $request = Request::create('/_whop/webhook', 'POST', content: $payload);
        $request->headers->set('webhook-id', $webhookId);
        $request->headers->set('webhook-timestamp', $timestamp);
        $request->headers->set('webhook-signature', 'v1,'.$signature);

        return $request;
    }
}
