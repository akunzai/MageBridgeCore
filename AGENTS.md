# MageBridge Core — Agent Guidelines

> Joomla 5/6 extension bridging Joomla CMS ↔ Magento/OpenMage.

## Quick Commands

| Task | Command |
|------|---------|
| Toolchain | `mise install` (@mise.toml) |
| Bundle | `composer bundle` |
| Lint / format | `composer lint` \| `composer fix` |
| Static analysis | `composer phpstan` (may need `--memory-limit=512M`) |
| Unit tests | `composer test` |
| Single test | `composer test -- tests/Unit/Helper/UrlHelperTest.php` |
| Coverage | `composer test-coverage` |
| Integration / Docker | @.devcontainer/AGENTS.md |
| E2E (Playwright) | @e2e/AGENTS.md |

## Architecture (DI / Service Providers)

| Extension type | Provider path |
|----------------|---------------|
| Site component | @joomla/components/com_magebridge/services/provider.php |
| Admin component | @joomla/administrator/components/com_magebridge/services/provider.php |
| Modules | `joomla/modules/mod_magebridge_*/services/provider.php` |
| Plugins | `joomla/plugins/*/*/services/provider.php` |
| Yireo library | @joomla/libraries/yireo/services/provider.php |

PSR-4: `MageBridge\Component\...`, `Yireo\` — see `composer.json` autoload.

## Progressive Disclosure (Context Offloading)

| Scope | Path |
|-------|------|
| Docker, live-sync, debug | @.devcontainer/AGENTS.md |
| Unit tests (Testable Implementation Pattern) | @tests/AGENTS.md |
| Playwright E2E | @e2e/AGENTS.md |
| Code patterns (ViewList/ViewForm, modern APIs) | @docs/development-patterns.md |
| Plugin service providers | @docs/plugin-providers.md |
| PathHelper (J5/J6 path SSOT) | @docs/joomla-v6-compat.md |
| Lessons learned & gotchas | @docs/lessons-learned.md |

## Project Constraints (non-derivable)

- **Language**: English only — code, PHPDoc, commits, markdown
- **PHP**: 8.3+, prefer `declare(strict_types=1)`; namespaces over legacy globals (`JFactory`, etc.)
- **Style**: PSR-12 via php-cs-fixer (`composer fix`); PHPDoc for public APIs and array shapes
- **Security**: Joomla input filters; exceptions over `die()`; never log secrets
- **Self-Reflection**: When non-obvious knowledge/gotchas are revealed, distill into a concise rule (≤ 2 bullets), promote to a target topic doc under `docs/` (or `docs/lessons-learned.md`), reference via Progressive Disclosure, and prune when stale.

## Claude Code Compatibility

> [!NOTE]
> `CLAUDE.md` is a symlink to `AGENTS.md`. All guidelines and updates must be made directly in `AGENTS.md`.

