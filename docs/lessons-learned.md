# Lessons Learned & Gotchas

Repository-wide collection of non-obvious knowledge, environment quirks, and lessons learned during development.

## Dependencies & Packages

- **[Composer]** Transitive "abandoned" notices for `laminas/laminas-loader`, `eloquent/enumeration`, `laminas/laminas-text` are safe to ignore unless Dependabot flags them — check with `gh api repos/akunzai/MageBridgeCore/dependabot/alerts` first ("abandoned" ≠ CVE).

## CI & Docker Build Performance

- **[Docker Layering]**: Isolate expensive compile stages (e.g. `php-ext` compilation) from application source or runtime dependencies in Dockerfiles to prevent cache invalidation when software versions change.
- **[BuildKit Cache Warming]**: Export GHA BuildKit caches on the default `main` branch via `docker/build-push-action` (`type=gha,mode=max`) to allow PR workflows to restore pre-warmed image caches on their first run.
