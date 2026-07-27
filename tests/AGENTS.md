# Unit Testing — Agent Guidelines

## Quick Commands

```bash
composer test
composer test -- tests/Unit/Helper/UrlHelperTest.php  # single file
composer test-coverage
```

## Testable Implementation Pattern

MageBridge is tightly coupled to the Joomla runtime. Unit tests isolate pure logic via **Testable Implementation**:

1. Add a `TestableXxx` class that mirrors production logic
2. Strip Joomla hard deps; inject state via properties/constructors
3. Assert business rules without booting CMS

Joomla-heavy seams (Cache, Route, Query, …) stay in E2E — @e2e/AGENTS.md.

## Layout

```text
tests/
├── bootstrap.php
└── Unit/{Controller,Helper,Model,Module,Plugin,Site,Library}/
```

Gold-standard specs: @tests/Unit/Helper/UrlHelperTest.php, @tests/Unit/Helper/EncryptionHelperTest.php
