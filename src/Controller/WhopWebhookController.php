<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Controller;

use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;
use Matchable\Whop\Exception\WebhookVerificationException;
use Matchable\Whop\Webhook\WebhookVerifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bundle-owned webhook endpoint. Verifies the Standard Webhooks signature with
 * the SDK's {@see WebhookVerifier}, decodes the JSON body, and delegates to the
 * configured {@see WhopWebhookHandlerInterface}.
 */
final readonly class WhopWebhookController
{
    private const array SIGNATURE_HEADERS = ['webhook-id', 'webhook-timestamp', 'webhook-signature'];

    public function __construct(
        private WebhookVerifier $verifier,
        private WhopWebhookHandlerInterface $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $rawPayload = $request->getContent();

        $headers = [];
        foreach (self::SIGNATURE_HEADERS as $name) {
            $value = $request->headers->get($name);
            if (null !== $value) {
                $headers[$name] = $value;
            }
        }

        try {
            $this->verifier->verify($rawPayload, $headers);
        } catch (WebhookVerificationException) {
            return new Response(status: Response::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = json_decode($rawPayload, associative: true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        if (!\is_array($decoded)) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $payload = [];
        foreach ($decoded as $key => $value) {
            if (\is_string($key)) {
                $payload[$key] = $value;
            }
        }

        $this->handler->handle($payload, $rawPayload);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
