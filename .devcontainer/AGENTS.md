# Devcontainer Agent Instructions

All commands are executed on the **Host machine**, not inside the container.
See also [README.md](README.md) for initial setup (TLS certs, hosts file, etc.).

## Starting and Stopping the Environment

```bash
docker compose -f .devcontainer/compose.yml up -d
docker compose -f .devcontainer/compose.yml down
docker compose -f .devcontainer/compose.yml exec -w /workspace joomla <command>
```

## Complete Reinstall of Joomla

```bash
.devcontainer/joomla/install.sh
```

This script will:
1. Install Joomla via CLI (if not already installed)
2. Check and install composer dependencies (if vendor directory doesn't exist)
3. Bundle the MageBridge extension (selectively copies production packages only)
4. Install the extension into Joomla
5. Configure MageBridge settings and enable plugins

**Note**: The bundling process only copies required production packages (brick, laminas, nikic, psr) from vendor, so development dependencies (PHPStan, PHPUnit, etc.) are not affected and remain available.

## Live Update Files to Container

Since Joomla's `/var/www/html` uses a Docker volume (not directly mounting the local directory), code changes need to be manually copied to the container to take effect.

```bash
# Copy a single file
docker compose -f .devcontainer/compose.yml cp \
  joomla/administrator/components/com_magebridge/src/View/Logs/HtmlView.php \
  joomla:/var/www/html/administrator/components/com_magebridge/src/View/Logs/HtmlView.php

# Copy multiple files (chain with &&)
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file1.php joomla:/var/www/html/path/to/file1.php && \
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file2.php joomla:/var/www/html/path/to/file2.php
```

**Notes**:
- Local code is mounted to `/workspace`, but Joomla actually runs from `/var/www/html`
- For quick testing of individual files, copy them to the container
- For complete fresh installation, use the `.devcontainer/joomla/install.sh` script

## Debugging & Troubleshooting

### View Joomla Error Logs

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/everything.php | tail -100

docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c "grep -i 'error_pattern' /var/www/html/administrator/logs/*.php | tail -20"

docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/everything.php
```

### View MageBridge Debug Logs

MageBridge has its own debug logging system. To enable it, set these values in Configuration > Debugging:
- **Debug**: Yes
- **Debug log**: Both database and file

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/magebridge.txt | tail -50

docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/magebridge.txt

# Query debug logs from database (table prefix: jos_)
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT timestamp, type, origin, message FROM jos_magebridge_log ORDER BY id DESC LIMIT 20;"
```

You can also view logs in Joomla Admin: **Components > MageBridge > Logs**

### Query Database Configuration

```bash
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT name, value FROM jos_magebridge_config WHERE name = 'api_widgets';"

docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e "SHOW TABLES LIKE '%magebridge%';"
```

### Clear Caches

```bash
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/cache/* /var/www/html/administrator/cache/*'
```

### Container Logs

```bash
docker compose -f .devcontainer/compose.yml logs joomla --tail=50
docker compose -f .devcontainer/compose.yml logs -f joomla
```
