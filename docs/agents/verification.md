# Verification

How an agent exercises a change in this repo before it reaches review.
Human setup narrative lives in `README.md`; this file holds only what
an agent needs.

## Starting the environment

```sh
composer test
```

<!-- drift:forge github -->
<!-- drift:entrypoint-cmd composer test -->

It never prompts. A step needing a human aborts non-zero naming the
prerequisite — see Human prerequisites below.

**Proof it ran**: PHPUnit tests exit 0 reporting `OK (658 tests, 1104 assertions)` or all unit tests passing.

## Checks

Composer scripts define the primary gate; see `composer.json` for task definitions.

| What | Command |
| --- | --- |
| Fast unit tests (gate) | `composer test` |
| Single unit test file | `composer test -- tests/Unit/Helper/UrlHelperTest.php` |
| Code style check | `composer lint` |
| Code style automatic fix | `composer fix` |
| Static analysis | `composer exec phpstan -- analyse --memory-limit=1G` |
| Extension package build | `composer bundle` |
| E2E test suite (Playwright) | `cd e2e && aube install && aubr test` |

## Human prerequisites

Run once, by a person. The start command fails until they are done.

- [ ] Install toolchain: `mise install` (PHP 8.4, Composer, Node.js, Aube)
- [ ] Trust local certificates for Docker stack: `.devcontainer/generate-certs.sh .secrets`
- [ ] Add local hosts entry: `127.0.0.1 www.dev.local store.dev.local` to `/etc/hosts`

## Ports

This stack is reached by hostname (`www.dev.local`, `store.dev.local`) and TLS, so ports cannot be offset. **Only one agent runs the environment at a time**; the lock is `.devcontainer/.lock`.

## Changes that need a deployed environment

These cannot be verified locally. Open the request as a draft, let the
pipeline deploy, then verify against CI:

- Multi-version Joomla 5 & 6 matrix: tested automatically in GitHub Actions CI (`.github/workflows/e2e.yml`)
- Live production Magento connections & payment gateways: live external services cannot run locally

Evidence from that environment cites the pipeline or deployment id and
the commit SHA, and is treated as containing real data: mask, crop, or
use a dedicated test account.

Agent may deploy to it: **no**.
Credentials come from GitHub repository secrets.

## Capturing evidence

- Recording: Playwright trace and video artifacts (`e2e/playwright-report`) — fallback: test terminal output and summary
- Screenshots: Playwright failure screenshots (`e2e/test-results`)

**This document is where the capture rules live**, and the request
document points here rather than restating them. A capture taken on the
developer's own machine carries their account's data, username, and home
paths as readily as a shared environment does. Assert on the frame, a
marker, or fixture data, and crop or mask what the tool happened to be
showing.

For a change behind a mode switch or feature flag, confirm the far end
received the call. A healthy container and a green build are not
evidence that an integration is wired up.

## Not verified

- Live third-party payment gateways and production Magento stores: covered by unit mocks and fixture data.
- Full multi-version Joomla matrix (Joomla 5.4.8 + Joomla 6.1.3): verified in GitHub Actions CI (`.github/workflows/e2e.yml`).

A gap you could have closed is not a gap. Run the check whose dependency
you have already seen running, and report a check you skipped as untried,
rather than recording it here as one this repo cannot run.
