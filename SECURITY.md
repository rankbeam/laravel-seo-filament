# Security Policy

This is the security policy for **`rankbeam/laravel-seo-filament`**, the free
Filament companion for [Rankbeam](https://rankbeam.dev). The core package
([`rankbeam/laravel-seo`](https://github.com/rankbeam/laravel-seo)) and the
commercial Pro package are distributed separately. Report an issue in the
package where the affected code lives.

## Supported versions

The current major (`1.x`) receives security fixes. Older versions are
end-of-life; upgrade to a supported release before reporting.

| Version | Supported |
|---|---|
| 1.x | Yes |
| Earlier versions | No |

Fixes ship in a normal patch release on the current major; see
[CHANGELOG.md](CHANGELOG.md) and [UPGRADING.md](UPGRADING.md).

## Reporting a vulnerability

**Please do not open a public GitHub issue for a security problem.** Email
[hello@rankbeam.dev](mailto:hello@rankbeam.dev) with `SECURITY` in the subject
line.

Please include, where you can:

- the affected package, package version, PHP version, Laravel version, and
  Filament version;
- a description of the vulnerability and its impact;
- a minimal reproduction or proof of concept;
- any suggested remediation.

## What to expect

Rankbeam is maintained by a small team, so timelines are best-effort rather than
a contractual SLA:

- We aim to acknowledge a report within a few business days.
- We will validate the issue, prepare a fix, and keep you updated on progress.
- We practise coordinated disclosure. Please allow reasonable time for a fix
  before publishing details; we will credit you in the release notes if you
  would like.

There is no paid bug-bounty programme at this time.

## Scope

In scope are vulnerabilities in this package's own code, such as unsafe output
in an SEO preview, insufficient validation of saved schema data, or unintended
exposure of fallback values.

Out of scope are application misconfiguration, vulnerabilities in Filament,
Laravel, Rankbeam core, or other dependencies, and issues that require an
already-compromised environment. Report dependency vulnerabilities to the
maintainer of the affected dependency. When in doubt, contact us privately and
we will triage the report.
