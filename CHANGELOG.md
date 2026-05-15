# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While `0.x.y` is in effect, **any** release may introduce breaking changes —
the public API is not stable until `1.0.0`.

## [Unreleased]

## [0.0.1] - 2026-05-15

### Added

- Initial pre-stable release.
- Autowired `WhopApiClient` and `WebhookVerifier` from the
  [Whop PHP SDK](https://github.com/devmatchable/whop-php-sdk) — consumers
  reach them via type-hint with no extra configuration.
- Compile-time-validated `whop:` configuration tree
  (`api_key`, `webhook_secret`, `base_url`, `http_client`, `webhook_path`).
- Bundle-owned `WhopWebhookController` mounted at the configurable
  `webhook_path` (default `/_whop/webhook`), returning `204` / `401` / `400`
  per the Standard Webhooks contract.
- `WhopWebhookReceivedEvent` for zero-config consumer subscription via
  `#[AsEventListener]`.
- Three documented handler-override paths: implement
  `WhopWebhookHandlerInterface`, extend `EventDispatchingWebhookHandler`, or
  decorate the default handler with `#[AsDecorator]`.
- Flex recipe targeting `symfony/recipes-contrib` — registers the bundle,
  drops `config/packages/whop.yaml` + `config/routes/whop.yaml`, and appends
  the required env vars to `.env`.

[Unreleased]: https://github.com/devmatchable/whop-symfony-bundle/compare/0.0.1...HEAD
[0.0.1]: https://github.com/devmatchable/whop-symfony-bundle/releases/tag/0.0.1
