# Security Policy

## Supported versions

This package is pre-1.0 and under active development. While 0.x is in effect, only the `master` branch receives security fixes. Pin to a specific commit if your project has stricter requirements.

Once 1.0.0 is released, this section will be updated with a supported-versions table.

## Reporting a vulnerability

**Please do not file public GitHub issues for security vulnerabilities.**

Use one of these private channels:

1. **GitHub private vulnerability reporting** (preferred) — submit a report via the repository's [Security tab](https://github.com/devmatchable/whop-symfony-bundle/security/advisories/new).
2. **Email** — send details to **tolu@matchable.dev**.

Please include:

- A description of the issue and its potential impact
- Steps to reproduce, or a proof-of-concept
- Affected versions, if known
- Any suggested mitigations

## Response timeline

- Acknowledgement within **72 hours** of receipt
- Initial assessment and severity classification within **7 days**
- For confirmed issues, a target fix date is communicated, after which we coordinate public disclosure (typically a GitHub Security Advisory paired with a release)

## Scope

**In scope:** the code in this repository and its direct configuration surface.

**Out of scope:** vulnerabilities in upstream dependencies (please report those to the respective project), and general operational misconfiguration in downstream consumers' applications.
