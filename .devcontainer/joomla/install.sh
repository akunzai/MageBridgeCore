#!/usr/bin/env bash

# shellcheck disable=1091
[[ -f .env ]] && source .env

echo "Checking database ..."
for _ in $(seq 1 20); do
  # shellcheck disable=2312
  docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} -e 'show databases;' 2>/dev/null" | grep -qF 'joomla' && break
  sleep 1
done

if ! docker compose exec joomla test -f configuration.php && docker compose exec joomla test -f installation/joomla.php; then
  # https://docs.joomla.org/J4.x:Joomla_CLI_Installation
  docker compose exec --user www-data joomla php installation/joomla.php install \
    --site-name DEMO \
    --admin-user ADMIN  \
    --admin-username "${ADMIN_USERNAME:-admin}" \
    --admin-password "${ADMIN_PASSWORD:-ChangeTheP@ssw0rd}" \
    --admin-email "${ADMIN_EMAIL:-admin@example.com}" \
    --db-host "${JOOMLA_DB_HOST:-mysql}" \
    --db-name "${JOOMLA_DB_NAME:-joomla}" \
    --db-user "${JOOMLA_DB_USER:-root}" \
    --db-pass "${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}}" \
    --db-prefix "${JOOMLA_DB_PREFIX:-vlqhe_}" \
    --no-interaction
fi

echo "Bundling extension ..."
docker compose exec -w /workspace joomla ./bundle.sh >/dev/null

# https://docs-next.joomla.org/docs/command-line-interface/using-the-cli/
if docker compose exec joomla test -f /var/www/html/cli/joomla.php; then

  if [[ "${1:-}" == "--force" ]]; then
    docker compose exec joomla bash -c "php /var/www/html/cli/joomla.php extension:list | grep -iE 'magebridge' | awk '{print \$2}' | xargs -I{} php /var/www/html/cli/joomla.php extension:remove -n {}" || true
  fi

  docker compose exec joomla php /var/www/html/cli/joomla.php extension:install --path /workspace/dist/pkg_magebridge.zip

  docker compose exec joomla php /var/www/html/cli/joomla.php cache:clean || true

  echo "Configuring logging ..."
  docker compose exec joomla php /var/www/html/cli/joomla.php config:set log_path=/var/www/html/administrator/logs
  if ! docker compose exec joomla grep -q "log_everything" /var/www/html/configuration.php; then
    docker compose exec joomla sed -i "s|^}$|\tpublic \$log_everything = 1;\n}|" /var/www/html/configuration.php
  fi

  echo "Enabling MageBridge plugins ..."
  DB_PREFIX="${JOOMLA_DB_PREFIX:-vlqhe_}"
  docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -e \"
    UPDATE ${DB_PREFIX}extensions SET enabled = 1
    WHERE type = 'plugin' AND element = 'magebridge'
    AND folder IN ('authentication', 'content', 'magebridge', 'magento', 'system', 'user');
  \"" 2>/dev/null

  echo "Configuring MageBridge ..."
  MAGEBRIDGE_HOST="${MAGEBRIDGE_HOST:-store.dev.local}"
  MAGEBRIDGE_API_USER="${MAGEBRIDGE_API_USER:-magebridge_api}"
  MAGEBRIDGE_API_KEY="${MAGEBRIDGE_API_KEY:-ChangeTheAp1K3y}"
  docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -e \"
    INSERT INTO ${DB_PREFIX}magebridge_config (name, value) VALUES
      ('host', '${MAGEBRIDGE_HOST}'),
      ('api_user', '${MAGEBRIDGE_API_USER}'),
      ('api_key', '${MAGEBRIDGE_API_KEY}'),
      ('protocol', 'https'),
      ('enforce_ssl', '3'),
      ('customer_group', '1'),
      ('usergroup', '2'),
      ('username_from_email', '1'),
      ('users_website_id', '1'),
      ('users_group_id', '1'),
      ('debug_log', '1')
    ON DUPLICATE KEY UPDATE value = VALUES(value);
  \"" 2>/dev/null

  echo "Creating MageBridge menu item ..."
  # Get MageBridge component_id
  # shellcheck disable=2312
  COMPONENT_ID=$(docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -sN -e 'SELECT extension_id FROM ${DB_PREFIX}extensions WHERE element = \"com_magebridge\" AND type = \"component\";'" 2>/dev/null | tr -d '[:space:]')

  # Check if Store menu item exists
  # shellcheck disable=2312
  MENU_EXISTS=$(docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -sN -e 'SELECT COUNT(*) FROM ${DB_PREFIX}menu WHERE alias = \"store\" AND menutype = \"mainmenu\";'" 2>/dev/null | tr -d '[:space:]')

  if [[ "${MENU_EXISTS}" == "0" ]] && [[ -n "${COMPONENT_ID}" ]]; then
    # Get max rgt value for nested set
    # shellcheck disable=2312
    MAX_RGT=$(docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -sN -e 'SELECT MAX(rgt) FROM ${DB_PREFIX}menu WHERE client_id = 0;'" 2>/dev/null | tr -d '[:space:]')
    NEW_LFT=$((MAX_RGT + 1))
    NEW_RGT=$((MAX_RGT + 2))

    docker compose exec mysql sh -c "mysql -u${JOOMLA_DB_USER:-root} -p${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${JOOMLA_DB_NAME:-joomla} -e 'INSERT INTO ${DB_PREFIX}menu (menutype, title, alias, path, link, type, published, parent_id, level, component_id, access, img, params, lft, rgt, home, language, client_id) VALUES (\"mainmenu\", \"Store\", \"store\", \"store\", \"index.php?option=com_magebridge&view=root\", \"component\", 1, 1, 1, ${COMPONENT_ID}, 1, \"\", \"{\\\"storeview\\\":\\\"default\\\"}\", ${NEW_LFT}, ${NEW_RGT}, 0, \"*\", 0);'" 2>/dev/null
    echo "Store menu item created"
  else
    echo "Store menu item already exists"
  fi

fi

echo "Fixing permissions ..."
docker compose exec joomla chown -R www-data:www-data /var/www/html