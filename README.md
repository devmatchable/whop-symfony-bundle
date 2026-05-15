# Whop Symfony Bundle

[![CI](https://github.com/devmatchable/whop-symfony-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/devmatchable/whop-symfony-bundle/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-2a6496)](https://phpstan.org/)

> [!WARNING]
> **This package is in active development and is not yet ready for production use.**
> The public API may change at any time before version `1.0.0` is released.
> Please do not depend on it in production projects until a stable release is published.

Symfony bundle for the [Whop PHP SDK](https://github.com/devmatchable/whop-php-sdk) —
autowires the `WhopApiClient` and `WebhookVerifier` from configuration and ships an
overridable, bundle-owned webhook controller.

## Requirements

- PHP 8.4+
- Symfony 7

## Installation

```bash
composer require devmatchable/whop-symfony-bundle
```

If [Symfony Flex](https://symfony.com/doc/current/setup/flex.html) is installed, the
recipe registers the bundle, drops the config and route files, and appends the
required environment variables automatically. Otherwise, follow the manual setup
below.

## Configuration

The bundle is configured under the `whop` key, conventionally in
`config/packages/whop.yaml`:

```yaml
whop:
    api_key:        '%env(WHOP_API_KEY)%'           # required
    webhook_secret: '%env(WHOP_WEBHOOK_SECRET)%'    # required
    base_url:       'https://api.whop.com/api/v1'   # optional, default shown
    http_client:    null                           # optional, default shown
    webhook_path:   '/_whop/webhook'                # optional, default shown
```

- **`api_key`** *(required)* — the Whop API key (Bearer token) the `WhopApiClient`
  authenticates with.
- **`webhook_secret`** *(required)* — the Standard Webhooks signing secret the
  `WebhookVerifier` checks incoming webhooks against. Supports both `whsec_`
  (production) and `ws_` (sandbox) secret formats.
- **`base_url`** *(optional)* — the Whop API base URL. Override with the sandbox URL
  (`https://sandbox-api.whop.com/api/v1`) for non-production environments.
- **`http_client`** *(optional)* — the service id of a PSR-18 / Symfony HTTP client
  to use for outbound API calls. When `null`, the bundle uses the default Symfony
  HTTP client.
- **`webhook_path`** *(optional)* — the path the bundle's webhook route is mounted
  at. Point your Whop webhook configuration at this path.

## Manual setup without the Flex recipe

1. Register the bundle in `config/bundles.php`:

   ```php
   return [
       // ...
       Matchable\Whop\Bundle\WhopBundle::class => ['all' => true],
   ];
   ```

2. Add `config/packages/whop.yaml` with the configuration block shown above.

3. Import the bundle's webhook route in `config/routes/whop.yaml`:

   ```yaml
   # config/routes/whop.yaml
   whop:
       resource: '@WhopBundle/config/routes.php'
   ```

## Usage — the API client

The SDK's `WhopApiClient` is registered as an autowired service. Type-hint it in any
service constructor:

```php
use Matchable\Whop\WhopApiClient;

final readonly class PaymentLookup
{
    public function __construct(
        private WhopApiClient $whop,
    ) {
    }

    public function fetch(string $paymentId): void
    {
        $payment = $this->whop->payments->get($paymentId);
        // ...
    }
}
```

The `WebhookVerifier` is autowired the same way if you need to verify webhooks
outside the bundle's controller.

## Usage — webhooks

Point your Whop webhook configuration at the bundle's webhook path (default
`/_whop/webhook`). The bundle-owned controller verifies the Standard Webhooks
signature, decodes the JSON body, and hands off to a `WhopWebhookHandlerInterface`.
The default handler dispatches a `WhopWebhookReceivedEvent`, so zero-config works:
just subscribe to the event.

```php
use Matchable\Whop\Bundle\Event\WhopWebhookReceivedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class WhopWebhookListener
{
    public function __invoke(WhopWebhookReceivedEvent $event): void
    {
        // $event->payload    — decoded webhook JSON (array<string, mixed>)
        // $event->rawPayload — the verified raw request body
    }
}
```

The controller responds with `204 No Content` on success, `401 Unauthorized` on an
invalid signature, and `400 Bad Request` on a body that is not decodable JSON.

## Overriding webhook handling

The webhook handler is overridable with standard Symfony service configuration — no
compiler pass, no tag. Three documented paths:

1. **Implement the interface** and alias `WhopWebhookHandlerInterface` to your
   service:

   ```yaml
   # config/services.yaml
   services:
       App\Whop\MyWebhookHandler: ~

       Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface:
           alias: App\Whop\MyWebhookHandler
   ```

2. **Extend** `EventDispatchingWebhookHandler` and override `handle()`.

3. **Decorate** the default handler with `#[AsDecorator]` to wrap it (e.g. log, then
   delegate to the inner handler).

## A note on DTOs

The SDK's DTOs (`Payment`, `WebhookResponse`, etc.) are sealed boundary types, and
the SDK has no DTO for *incoming* webhook event payloads — `WebhookResponse` models
the response from *creating* a webhook registration, not an event body. The handler
therefore receives the decoded `array<string, mixed> $payload`. Map it to your own
domain type inside your listener or handler.

## License

MIT — see [LICENSE](LICENSE).
