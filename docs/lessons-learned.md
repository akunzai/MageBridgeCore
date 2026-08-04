# Lessons Learned & Gotchas

Repository-wide collection of non-obvious knowledge, environment quirks, and lessons learned during development.

## Dependencies & Packages

- **[Composer]** Transitive "abandoned" notices for `laminas/laminas-loader`, `eloquent/enumeration`, `laminas/laminas-text` are safe to ignore unless Dependabot flags them — check with `gh api repos/akunzai/MageBridgeCore/dependabot/alerts` first ("abandoned" ≠ CVE).

