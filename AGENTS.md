# AGENTS.md — Whop Symfony Bundle Integration Guide

A dense reference for AI coding agents working on, or integrating,
`devmatchable/whop-symfony-bundle`. Read this in full before generating code.

---

## What this package is

A Symfony 7 bundle that layers framework integration on top of the
framework-agnostic [`devmatchable/whop-php-sdk`](https://github.com/devmatchable/whop-php-sdk).
It does three things:

- Autowires the SDK's `Matchable\Whop\WhopApiClient` and
  `Matchable\Whop\Webhook\WebhookVerifier` as services, driven by configuration.
- Ships a bundle-owned, overridable webhook controller that verifies the Standard
  Webhooks signature and delegates to a `WhopWebhookHandlerInterface`.
- Provides a public Symfony Flex recipe (staged separately, not part of this repo).

PHP namespace: `Matchable\Whop\Bundle\`.

```bash
composer require devmatchable/whop-symfony-bundle
```

The bundle `require`s the SDK. The SDK is not yet on Packagist — consumers add a
`repositories` VCS entry for `devmatchable/whop-php-sdk` (see `README.md`).

---

## The SDK boundary — do not cross it

**This bundle must never gain logic that belongs in the SDK.** The SDK is the
framework-agnostic core: HTTP transport, resource methods, DTOs, webhook signature
verification. The bundle is *only* glue:

- Service wiring (`config/services.php`).
- Configuration tree (`src/WhopBundle.php`).
- A Symfony controller, event, and handler contract.

If you find yourself wanting to add API logic, DTO hydration, or signature handling
here — that belongs in `devmatchable/whop-php-sdk`. SDK DTOs are sealed
(`final readonly`, private constructors); the bundle does not add a hydration seam.
Apps map the decoded webhook `array $payload` to their own types in their listener.

---

## Layout convention

Config lives in `config/` **at the repo root**, not `src/Resources/config/`. This is
the modern bundle convention: `AbstractBundle::getPath()` strips the `src/` segment,
so both `@WhopBundle/config/...` and `loadExtension`'s `../config/...` resolve to the
root `config/` directory.

```
config/
  services.php   # service definitions
  routes.php     # webhook route, bound to %whop.webhook_path%
src/
  WhopBundle.php                          # AbstractBundle: config tree + loadExtension
  Controller/WhopWebhookController.php     # __invoke: verify -> handler -> Response
  Webhook/WhopWebhookHandlerInterface.php  # handle(array $payload, string $rawPayload): void
  Webhook/EventDispatchingWebhookHandler.php
  Event/WhopWebhookReceivedEvent.php
tests/
  Kernel/TestKernel.php                    # bootable micro-kernel for integration tests
  Fixtures/RecordingWebhookHandler.php
```

**Routes are a PHP file, not XML.** `symfony/routing` 8.0 removed the XML
configuration loader, so `config/routes.php` uses the `RoutingConfigurator` API.

---

## Service wiring notes

- `whop.http_client` is a `Symfony\Component\HttpClient\Psr18Client` wrapping the
  inner HTTP client (the framework default, or the `whop.http_client`-configured
  service when set). `Psr18Client` also implements PSR-17
  `RequestFactoryInterface` / `StreamFactoryInterface`, so it is passed for all three
  SDK constructor slots — no `php-http/discovery` runtime dependency.
- `WhopApiClient` and `WebhookVerifier` are registered **public** — they are the
  bundle's public API surface, type-hinted by consumers and retrieved directly in
  tests.
- `WhopWebhookHandlerInterface` is aliased to `EventDispatchingWebhookHandler` by
  default. Apps override by re-aliasing the interface, extending the default class,
  or using `#[AsDecorator]`.

---

## Running the quality gates

```bash
composer test   # PHPUnit — unit + integration (TestKernel boots the bundle)
composer stan   # PHPStan at level max
composer cs     # php-cs-fixer dry-run
composer cs:fix # php-cs-fixer apply
```

CI (`.github/workflows/ci.yml`) runs `cs`, `stan`, and `test` on PHP 8.4. All three
must pass. PHPStan runs at `level: max` — fix the code, do not weaken the level.
The php-cs-fixer rule set is kept consistent with the SDK's.

---

## Do / don't

**Do:**
- Keep the bundle a thin integration layer over the SDK.
- Configure via the `whop` config tree; surface new options there, not as magic
  service ids.
- Write integration tests through `TestKernel` — it proves the wiring, the route,
  and the override path actually work end to end.

**Don't:**
- Don't add API-call logic, DTOs, or webhook signature code here — that is the SDK's
  job.
- Don't reintroduce `config/routes.xml` — `symfony/routing` 8.0 dropped the XML
  loader.
- Don't make services public unless they are genuinely part of the consumer-facing
  API.
