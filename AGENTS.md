# Agent Guidelines for MageBridge Core

> MageBridge is a Joomla 5 extension for integrating Joomla CMS with Magento/OpenMage e-commerce platform.

---

## Documentation Principles

1. **Keep root AGENTS.md concise** - Only essential, cross-cutting knowledge belongs here. Domain-specific knowledge should be placed in `AGENTS.md` files within relevant subdirectories or in dedicated markdown files under `docs/`
2. **Record valuable knowledge when solving problems** - When discovering useful patterns or solutions, document them in the most relevant file
3. **Never modify CLAUDE.md directly** - It is a symlink to AGENTS.md
4. **All files and code must be written in English** - Including PHPDoc, inline comments, commit messages, and markdown files

## Documentation Map

### Subdirectory AGENTS.md

- [.devcontainer/AGENTS.md](.devcontainer/AGENTS.md) - Docker environment, debugging, and troubleshooting
- [tests/AGENTS.md](tests/AGENTS.md) - Unit testing strategy
- [e2e/AGENTS.md](e2e/AGENTS.md) - E2E testing with Playwright

### Development Knowledge (docs/)

- [Development Patterns](docs/development-patterns.md) - Code patterns, namespace conventions, and technical reference
- [Plugin Service Providers](docs/plugin-providers.md) - Joomla 5 plugin service provider pattern
- [Joomla v6 Compatibility](docs/joomla-v6-compat.md) - PathHelper pattern for forward compatibility

---

## Build & Verify

- `./bundle.sh` - Bundle the extension
- `composer lint` - PHP CS Fixer dry-run check
- `composer fix` - Auto-format code
- `composer run phpstan` - Static analysis (may require `--memory-limit=512M`)
- `composer test` - Unit tests (PHPUnit)
- Run integration tests via Docker environment (see [.devcontainer/AGENTS.md](.devcontainer/AGENTS.md))
- E2E tests with Playwright (see [e2e/AGENTS.md](e2e/AGENTS.md))

## Code Style & Types

- PSR-12 coding standard
- PHP 8.3+, use strict types whenever possible
- Always declare namespaces and imports, avoid legacy global classes
- Use PHPDoc to annotate public APIs and provide meaningful comments for array shapes
- Use `use` statements for imports, avoid FQCN in PHPDoc

## Error & Security

- Validate input via Joomla filters
- Prefer exception handling over die()
- Never log sensitive information
- Use Joomla logging helpers

## Key Files Reference

### Component Service Providers
- `/joomla/components/com_magebridge/services/provider.php`
- `/joomla/administrator/components/com_magebridge/services/provider.php`

### Module Service Providers
- `/joomla/modules/mod_magebridge_*/services/provider.php`

### Plugin Service Providers
- `/joomla/plugins/*/*/services/provider.php`

### Library Service Provider
- `/joomla/libraries/yireo/services/provider.php`
