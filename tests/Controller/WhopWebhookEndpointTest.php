<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Controller;

use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use Matchable\Whop\Bundle\Tests\Kernel\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhopWebhookEndpointTest extends TestCase
{
    private const string SECRET = 'ws_endpoint_secret';

    public function testValidWebhookReturns204AndDispatchesEvent(): void
    {
        $kernel = $this->bootKernel();

        $received = null;
        $kernel->getContainer()->get('event_dispatcher')->addListener(
            WhopWebhookReceivedEvent::class,
            static function (WhopWebhookReceivedEvent $event) use (&$received): void {
                $received = $event;
            },
        );

        $payload = '{"event":"payment.created","data":{"id":"pay_123"}}';
        $response = $kernel->handle(self::signedRequest($payload));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertInstanceOf(WhopWebhookReceivedEvent::class, $received);
        self::assertSame(
            ['event' => 'payment.created', 'data' => ['id' => 'pay_123']],
            $received->payload,
        );

        $this->cleanUp($kernel);
    }

    public function testInvalidSignatureReturns401(): void
    {
        $kernel = $this->bootKernel();

        $request = Request::create('/_whop/webhook', 'POST', content: '{"event":"x"}');
        $request->headers->set('webhook-id', 'msg_1');
        $request->headers->set('webhook-timestamp', (string) time());
        $request->headers->set('webhook-signature', 'v1,not-valid');

        $response = $kernel->handle($request);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->cleanUp($kernel);
    }

    public function testMalformedJsonReturns400(): void
    {
        $kernel = $this->bootKernel();

        $response = $kernel->handle(self::signedRequest('{not json'));

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->cleanUp($kernel);
    }

    private function bootKernel(): TestKernel
    {
        $kernel = new TestKernel(['api_key' => 'apik_test', 'webhook_secret' => self::SECRET]);
        $kernel->boot();

        return $kernel;
    }

    private function cleanUp(TestKernel $kernel): void
    {
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
