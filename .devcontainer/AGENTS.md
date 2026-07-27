# Devcontainer — Agent Guidelines

Run all commands on the **host**, not inside the container.
Initial setup (TLS, hosts): @.devcontainer/README.md

## Lifecycle

```bash
docker compose -f .devcontainer/compose.yml up -d
docker compose -f .devcontainer/compose.yml down
docker compose -f .devcontainer/compose.yml exec -w /workspace joomla <command>
```

### Full Joomla reinstall (install → vendor → bundle → configure)

```bash
.devcontainer/joomla/install.sh
```

Bundling copies production packages only (`brick`, `laminas`, `nikic`, `psr`); dev tooling (PHPStan, PHPUnit) stays on the host vendor tree.

## Live-sync (volume ≠ runtime)

`/workspace` is the bind mount; Joomla serves `/var/www/html` (volume). After local edits, `cp` into the container:

```bash
# single file
docker compose -f .devcontainer/compose.yml cp \
  joomla/administrator/components/com_magebridge/src/View/Logs/HtmlView.php \
  joomla:/var/www/html/administrator/components/com_magebridge/src/View/Logs/HtmlView.php

# multi-file: chain with &&
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file1.php joomla:/var/www/html/path/to/file1.php && \
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file2.php joomla:/var/www/html/path/to/file2.php
```

Full reinstall when live-sync is insufficient: `.devcontainer/joomla/install.sh`.

## Debug SOP

### Joomla logs

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/everything.php | tail -100

docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c "grep -i 'error_pattern' /var/www/html/administrator/logs/*.php | tail -20"

docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/everything.php
```

### MageBridge debug log

Enable in Configuration → Debugging: **Debug** = Yes, **Debug log** = Both database and file.

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/magebridge.txt | tail -50

docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/magebridge.txt

# DB log (prefix jos_)
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT timestamp, type, origin, message FROM jos_magebridge_log ORDER BY id DESC LIMIT 20;"
```

Admin UI: **Components → MageBridge → Logs**

### Config / schema probes

```bash
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT name, value FROM jos_magebridge_config WHERE name = 'api_widgets';"

docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e "SHOW TABLES LIKE '%magebridge%';"
```

### Cache bust / container logs

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/cache/* /var/www/html/administrator/cache/*'

docker compose -f .devcontainer/compose.yml logs joomla --tail=50
docker compose -f .devcontainer/compose.yml logs -f joomla
```
