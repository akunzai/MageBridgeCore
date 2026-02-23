# Testing Agent Instructions

## Commands

```bash
composer test
composer test-coverage
```

## Unit Testing Strategy

Since MageBridge classes are highly dependent on the Joomla environment, we use the **Testable Implementation Pattern**:

1. Create `TestableXxx` classes that replicate core logic
2. Remove Joomla dependencies, use injectable properties instead
3. Test pure business logic without requiring the full Joomla environment

Classes highly dependent on the Joomla environment (Cache, Route, Query, etc.) are covered through E2E tests (see [e2e/AGENTS.md](../e2e/AGENTS.md)).

## File Structure

```text
tests/
├── bootstrap.php
└── Unit/
    ├── Controller/
    ├── Helper/
    ├── Model/
    ├── Module/
    ├── Plugin/
    ├── Site/
    └── Library/
```
