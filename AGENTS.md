# Agent Instructions for MageBridge Core

1. Build & Verify:
   - `./bundle.sh` bundles extension
   - `composer lint` (PHP CS Fixer dry-run)
   - `composer fix` auto-formats
   - `composer run phpstan` (level per phpstan.neon)
   - Run targeted tests through Joomla CLI once Docker stack is up
2. Devcontainer commands:
   - `docker compose -f .devcontainer/compose.yml up -d`
   - Add `-f ... exec -w /workspace joomla <command>` for composer or scripts
3. Code Style & Types:
   - PSR-12, PHP 8.1+, strict types when feasible
   - Always declare namespaces/imports; avoid legacy global classes
   - Use PHPDoc for public APIs and annotate array shapes meaningfully
4. Error & Security:
   - Validate input via Joomla filters; prefer exceptions over die()
   - Never log secrets; rely on Joomla logging helpers
