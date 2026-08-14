# MageBridge Core — Agent Guidelines

MageBridge Core is a Joomla 5/6 extension that bridges Joomla CMS with Magento/OpenMage.

Use `mise install` for the toolchain. Composer scripts define the build and quality checks; `composer bundle` builds the distributable package.

## Pointers

- Docker, live sync, and integration debugging: @.devcontainer/AGENTS.md
- Unit-test conventions and gold-standard tests: @tests/AGENTS.md
- Playwright E2E conventions and gold-standard tests: @e2e/AGENTS.md
- Joomla implementation patterns: @docs/development-patterns.md
- Plugin service providers: @docs/plugin-providers.md
- Joomla 5/6 path compatibility: @docs/joomla-v6-compat.md
- Repository gotchas: @docs/lessons-learned.md

## Project Constraint

Write code, PHPDoc, commits, and Markdown in English.

## Self-Reflection

- **Candidate**: Distill a non-obvious gotcha into no more than two context-tagged bullets and propose it before writing.
- **Promote**: After confirmation, merge it into an existing topic document or create `docs/<topic>.md`; use `docs/lessons-learned.md` only as a fallback, and keep one pointer per document above.
- **Prune**: Propose removing entries when they become obsolete, enforced, duplicated, or merely record a debugging transcript.

## Claude Code Compatibility

`CLAUDE.md` is a symbolic link to `AGENTS.md`. Edit `AGENTS.md` directly.
