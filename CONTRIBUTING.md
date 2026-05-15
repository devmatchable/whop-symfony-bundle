# Contributing to whop-symfony-bundle

Thanks for taking the time to contribute. This document covers the basics of working on the bundle.

## Reporting issues

Please use [GitHub Issues](https://github.com/devmatchable/whop-symfony-bundle/issues/new/choose) and pick the appropriate template. Include the PHP version, Symfony version, and a minimal reproduction wherever possible.

For security-sensitive reports, see [SECURITY.md](SECURITY.md) — please do not file public issues for vulnerabilities.

## Suggesting features

Open a feature request issue describing the use case and what you'd expect the API to look like. The bundle is intentionally small and aligned with the underlying [whop-php-sdk](https://github.com/devmatchable/whop-php-sdk) — features that belong upstream in the SDK should be proposed there instead.

## Local development

```bash
git clone https://github.com/devmatchable/whop-symfony-bundle.git
cd whop-symfony-bundle
composer install
```

Requires PHP 8.4+ and Composer 2.

## Quality gates

The same checks CI runs:

```bash
composer cs       # PHP-CS-Fixer (dry run + diff)
composer cs:fix   # Auto-fix style violations
composer stan     # PHPStan at level max
composer test     # PHPUnit
```

CS-Fixer and PHPStan run on the highest-deps install only; PHPUnit runs on both the highest and lowest installs. All three must pass on the highest matrix before a PR can be merged.

## Pull request workflow

1. Fork the repo and create a branch from `master`.
2. Make your change — keep PRs focused on a single logical change.
3. Add or update tests covering the change.
4. Run the quality gates above locally.
5. Open a PR using the [pull request template](.github/PULL_REQUEST_TEMPLATE.md).
6. CI will run automatically. Address any reviewer feedback in follow-up commits.

## Commit messages

Conventional commit prefixes (`feat:`, `fix:`, `docs:`, `chore:`, `ci:`, `refactor:`, `test:`) are encouraged. Keep the subject line under 72 characters and explain the *why* in the body when the change is non-obvious.

## Code of Conduct

By participating in this project, you agree to abide by the project's [Code of Conduct](CODE_OF_CONDUCT.md).
