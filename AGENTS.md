# MageBridge Core — Agent Guidelines

MageBridge Core is a Joomla 5/6 extension that bridges Joomla CMS with Magento/OpenMage.

Use `mise install` for the toolchain. Composer scripts define the build and quality checks; `composer bundle` builds the distributable package.

## Pointers

- Docker, live sync, and integration debugging: `.devcontainer/AGENTS.md`
- Unit-test conventions and gold-standard tests: `tests/AGENTS.md`
- Playwright E2E conventions and gold-standard tests: `e2e/AGENTS.md`
- Joomla implementation patterns: `docs/agents/development-patterns.md`
- Plugin service providers: `docs/agents/plugin-providers.md`
- Joomla 5/6 path compatibility: `docs/agents/joomla-v6-compat.md`
- Repository gotchas: `docs/agents/lessons-learned.md`
- When filing or triaging an issue, read `docs/agents/issue-tracker.md`
- When opening a pull or merge request, read `docs/agents/pull-request.md`
- Before running or reporting verification, read `docs/agents/verification.md`
- Triage labels: `docs/agents/triage-labels.md`
- Domain docs (single-context): `docs/agents/domain.md`

## Project Constraint

Write code, PHPDoc, commits, and Markdown in English.

## Prevent Recurrence

- **Candidate**: Name who hits this again, in which file, on what change. No such scenario, nothing to propose.
- **Promote**: Offer the first tier that reaches them and only that one, pending confirmation — enforce it (assert/type/test) with its size quoted, else a comment at that site, else an agent-facing doc (`docs/agents/<topic>.md`, else `docs/agents/lessons-learned.md`) with one backtick-path line under Pointers and one sentence on why the tiers above cannot hold it.
- **Prune**: When adding to a file, audit the rest of it in the same pass. Drop entries once stale (obsolete version, now enforced, duplicated, or a transcript) — not by a fixed count.
