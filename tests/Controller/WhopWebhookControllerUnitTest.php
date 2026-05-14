<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Controller;

use Matchable\Whop\Bundle\Controller\WhopWebhookController;
use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;
use Matchable\Whop\Webhook\WebhookVerifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhopWebhookControllerUnitTest extends TestCase
{
    private const string SECRET = 'ws_unit_secret';

    public function testValidWebhookIsVerifiedDecodedAndHandled(): void
    {
        $handler = $this->recordingHandler();
        $controller = new WhopWebhookController(new WebhookVerifier(self::SECRET), $handler);

        $payload = '{"event":"payment.created","data":{"id":"pay_1"}}';
        $response = $controller(self::signedRequest($payload));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertCount(1, $handler->received);
        self::assertSame(
            ['event' => 'payment.created', 'data' => ['id' => 'pay_1']],
            $handler->received[0]['payload'],
        );
        self::assertSame($payload, $handler->received[0]['rawPayload']);
    }

    public function testInvalidSignatureReturns401AndDoesNotHandle(): void
    {
        $handler = $this->recordingHandler();
        $controller = new WhopWebhookController(new WebhookVerifier(self::SECRET), $handler);

        $request = Request::create('/_whop/webhook', 'POST', content: '{"event":"x"}');
        $request->headers->set('webhook-id', 'msg_1');
        $request->headers->set('webhook-timestamp', (string) time());
        $request->headers->set('webhook-signature', 'v1,deadbeef');

        $response = $controller($request);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertCount(0, $handler->received);
    }

    public function testMalformedJsonReturns400AndDoesNotHandle(): void
    {
        $handler = $this->recordingHandler();
        $controller = new WhopWebhookController(new WebhookVerifier(self::SECRET), $handler);

        $response = $controller(self::signedRequest('{not valid json'));

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertCount(0, $handler->received);
    }

    private function recordingHandler(): WhopWebhookHandlerInterface
    {
        return new class implements WhopWebhookHandlerInterface {
            /** @var list<array{payload: array<string, mixed>, rawPayload: string}> */
            public array $received = [];

            public function handle(array $payload, string $rawPayload): void
            {
                $this->received[] = ['payload' => $payload, 'rawPayload' => $rawPayload];
            }
        };
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
