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

## Project Constraints (non-derivable)

- **Language**: English only — code, PHPDoc, commits, markdown
- **PHP**: 8.3+, prefer `declare(strict_types=1)`; namespaces over legacy globals (`JFactory`, etc.)
- **Style**: PSR-12 via php-cs-fixer (`composer fix`); PHPDoc for public APIs and array shapes
- **Security**: Joomla input filters; exceptions over `die()`; never log secrets
- **Knowledge writeback**: propose durable gotchas to the nearest `AGENTS.md` or `docs/` (SSOT — never edit `CLAUDE.md` directly)

## Lessons Learned (actively pruned, max 5)

- **[Composer]** Transitive "abandoned" notices for `laminas/laminas-loader`, `eloquent/enumeration`, `laminas/laminas-text` are safe to ignore unless Dependabot flags them — check with `gh api repos/akunzai/MageBridgeCore/dependabot/alerts` first ("abandoned" ≠ CVE)

## Claude Code Compatibility

> [!NOTE]
> This repository maintains compatibility with Claude Code. The file `CLAUDE.md` is a symbolic link pointing to `AGENTS.md`.
> All commands, style guides, and workflows defined in `AGENTS.md` apply to both Antigravity (and other agentic assistants) and Claude Code.
> **DO NOT** delete the `CLAUDE.md` symbolic link or edit it independently; all guidelines must be updated directly in `AGENTS.md`.
