#!/usr/bin/env bash

# Set default compose file relative to this script's location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export COMPOSE_FILE="${COMPOSE_FILE:-${SCRIPT_DIR}/../compose.yml}"
# Disable TTY if stdin or stdout is not a terminal
TTY_FLAG=""
if [[ ! -t 0 ]] || [[ ! -t 1 ]]; then
  TTY_FLAG="-T"
fi

# Helper function for docker compose exec
dc_exec() {
  # shellcheck disable=SC2086
  docker compose exec ${TTY_FLAG} "$@"
}

# shellcheck disable=1091
[[ -f "${SCRIPT_DIR}/.env" ]] && source "${SCRIPT_DIR}/.env"

# Install CA certificate for SSL connections to OpenMage
if dc_exec joomla test -f /run/secrets/ca.pem; then
  echo "Installing CA certificate for MageBridge SSL connections ..."
  dc_exec joomla cp /run/secrets/ca.pem /usr/local/share/ca-certificates/magebridge-ca.crt
  dc_exec joomla update-ca-certificates
fi

ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-ChangeTheP@ssw0rd}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"
JOOMLA_DB_HOST="${JOOMLA_DB_HOST:-mysql}"
JOOMLA_DB_NAME="${JOOMLA_DB_NAME:-joomla}"
JOOMLA_DB_PREFIX="${JOOMLA_DB_PREFIX:-jos_}"
JOOMLA_DB_PASSWORD="${JOOMLA_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}}"
JOOMLA_DB_USER="${JOOMLA_DB_USER:-root}"
JOOMLA_COOKIE_DOMAIN="${JOOMLA_COOKIE_DOMAIN:-}"
MAGEBRIDGE_HOST="${MAGEBRIDGE_HOST:-store.dev.local}"
MAGEBRIDGE_API_USER="${MAGEBRIDGE_API_USER:-magebridge_api}"
MAGEBRIDGE_API_KEY="${MAGEBRIDGE_API_KEY:-ChangeTheAp1K3y}"

# Helper function for MySQL operations
joomla_mysql() {
  # Always use -T for MySQL operations (no TTY needed)
  # shellcheck disable=SC2086
  docker compose exec -T -e MYSQL_PWD="${JOOMLA_DB_PASSWORD}" mysql mysql -u"${JOOMLA_DB_USER}" "${JOOMLA_DB_NAME}" "$@"
}

echo "Checking database ..."
for _ in $(seq 1 20); do
  # Always use -T for MySQL operations (no TTY needed)
  # shellcheck disable=SC2086,SC2312
  docker compose exec -T -e MYSQL_PWD="${MYSQL_ROOT_PASSWORD:-secret}" mysql mysql -uroot -e 'show databases;' 2>/dev/null | grep -qF 'joomla' && break
  sleep 1
done

if ! dc_exec joomla test -f configuration.php && dc_exec joomla test -f installation/joomla.php; then
  # https://docs-next.joomla.org/docs/command-line-interface/joomla-cli-installation
  dc_exec --user www-data joomla php installation/joomla.php install \
    --site-name DEMO \
    --admin-user ADMIN  \
    --admin-username "${ADMIN_USERNAME}" \
    --admin-password "${ADMIN_PASSWORD}" \
    --admin-email "${ADMIN_EMAIL}" \
    --db-host "${JOOMLA_DB_HOST}" \
    --db-name "${JOOMLA_DB_NAME}" \
    --db-user "${JOOMLA_DB_USER}" \
    --db-pass "${JOOMLA_DB_PASSWORD}" \
    --db-prefix "${JOOMLA_DB_PREFIX}" \
    --no-interaction
fi

echo "Checking composer dependencies ..."
if ! dc_exec -w /workspace joomla test -d vendor; then
  echo "Installing composer dependencies ..."
  dc_exec -w /workspace joomla composer install --quiet
fi

echo "Bundling extension ..."
dc_exec -w /workspace joomla ./bundle.sh >/dev/null

# https://docs-next.joomla.org/docs/command-line-interface/using-the-cli/
if dc_exec joomla test -f /var/www/html/cli/joomla.php; then

  if [[ "${1:-}" == "--force" ]]; then
    dc_exec joomla bash -c "php /var/www/html/cli/joomla.php extension:list | grep -iE 'magebridge' | awk '{print \$2}' | xargs -I{} php /var/www/html/cli/joomla.php extension:remove -n {}" || true
  fi

  dc_exec joomla php /var/www/html/cli/joomla.php extension:install --path /workspace/dist/pkg_magebridge.zip

  dc_exec joomla php /var/www/html/cli/joomla.php cache:clean || true

  echo "Configuring Joomla settings ..."
  dc_exec joomla php /var/www/html/cli/joomla.php config:set log_path=/var/www/html/administrator/logs
  dc_exec joomla php /var/www/html/cli/joomla.php config:set sef_rewrite=true
  if ! dc_exec joomla grep -q "log_everything" /var/www/html/configuration.php; then
    dc_exec joomla sed -i "s|^}$|\tpublic \$log_everything = 1;\n}|" /var/www/html/configuration.php
  fi

  if [[ -n "${JOOMLA_COOKIE_DOMAIN}" ]]; then
    echo "Configuring cookie domain ..."
    if ! dc_exec joomla grep -q "cookie_domain" /var/www/html/configuration.php; then
      dc_exec joomla sed -i "s|^}$|\tpublic \$cookie_domain = '${JOOMLA_COOKIE_DOMAIN}';\n}|" /var/www/html/configuration.php
    else
      dc_exec joomla sed -i "s|public \$cookie_domain = .*|public \$cookie_domain = '${JOOMLA_COOKIE_DOMAIN}';|" /var/www/html/configuration.php
    fi
  fi

  echo "Enabling MageBridge plugins ..."

  joomla_mysql -e "
    UPDATE ${JOOMLA_DB_PREFIX}extensions SET enabled = 1
    WHERE type = 'plugin' AND element = 'magebridge'
    AND folder IN ('authentication', 'content', 'magebridge', 'magento', 'system', 'user');
    UPDATE ${JOOMLA_DB_PREFIX}extensions SET enabled = 1
    WHERE type = 'plugin' AND element = 'magebridgepre' AND folder = 'system';
  " 2>/dev/null

  echo "Configuring MageBridge ..."

  # First, add UNIQUE index on name column if it doesn't exist
  joomla_mysql -e "
    SELECT COUNT(*) INTO @index_exists
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = '${JOOMLA_DB_PREFIX}magebridge_config'
      AND index_name = 'name';

    SET @sql = IF(@index_exists = 0,
      'ALTER TABLE ${JOOMLA_DB_PREFIX}magebridge_config ADD UNIQUE KEY name (name)',
      'SELECT \"Index already exists\" AS message');

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  " 2>/dev/null || true

  # Now insert/update configuration values
  joomla_mysql -e "
    INSERT INTO ${JOOMLA_DB_PREFIX}magebridge_config (name, value) VALUES
      ('host', '${MAGEBRIDGE_HOST}'),
      ('api_user', '${MAGEBRIDGE_API_USER}'),
      ('api_key', '${MAGEBRIDGE_API_KEY}'),
      ('protocol', 'https'),
      ('enforce_ssl', '1'),
      ('website', '1'),
      ('customer_group', '1'),
      ('usergroup', '2'),
      ('username_from_email', '1'),
      ('users_website_id', '1'),
      ('users_group_id', '1'),
      ('debug', '1'),
      ('debug_log', 'both'),
      ('api_widgets', '1'),
      ('load_stores', '1'),
      ('disable_js_all', '0'),
      ('enable_sso', '1'),
      ('bridge_cookie_all', '1')
    ON DUPLICATE KEY UPDATE value = VALUES(value);
  " 2>/dev/null

  echo "Creating MageBridge menu item ..."
  # Get MageBridge component_id
  # shellcheck disable=2312
  COMPONENT_ID=$(joomla_mysql -sN -e "SELECT extension_id FROM ${JOOMLA_DB_PREFIX}extensions WHERE element = 'com_magebridge' AND type = 'component';" 2>/dev/null | tr -d '[:space:]')

  # Check if Store menu item exists
  # shellcheck disable=2312
  MENU_EXISTS=$(joomla_mysql -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}menu WHERE alias = 'store' AND menutype = 'mainmenu';" 2>/dev/null | tr -d '[:space:]')

  if [[ "${MENU_EXISTS}" == "0" ]] && [[ -n "${COMPONENT_ID}" ]]; then
    # Get max rgt value for nested set
    # shellcheck disable=2312
    MAX_RGT=$(joomla_mysql -sN -e "SELECT MAX(rgt) FROM ${JOOMLA_DB_PREFIX}menu WHERE client_id = 0;" 2>/dev/null | tr -d '[:space:]')
    NEW_LFT=$((MAX_RGT + 1))
    NEW_RGT=$((MAX_RGT + 2))

    joomla_mysql -e "INSERT INTO ${JOOMLA_DB_PREFIX}menu (menutype, title, alias, path, link, type, published, parent_id, level, component_id, access, img, params, lft, rgt, home, language, client_id) VALUES ('mainmenu', 'Store', 'store', 'store', 'index.php?option=com_magebridge&view=root', 'component', 1, 1, 1, ${COMPONENT_ID}, 1, '', '{\"storeview\":\"default\"}', ${NEW_LFT}, ${NEW_RGT}, 0, '*', 0);" 2>/dev/null
    echo "Store menu item created"
  else
    echo "Store menu item already exists, updating storeview ..."
    # Update existing menu item to ensure storeview is set
    joomla_mysql -e "
      UPDATE ${JOOMLA_DB_PREFIX}menu
      SET params = '{\"storeview\":\"default\"}'
      WHERE alias = 'store' AND menutype = 'mainmenu' AND link LIKE '%com_magebridge%';
    " 2>/dev/null
  fi

  echo "Enabling MageBridge Cart module ..."
  # Get Cart module extension_id
  # shellcheck disable=2312
  CART_MODULE_ID=$(joomla_mysql -sN -e "SELECT extension_id FROM ${JOOMLA_DB_PREFIX}extensions WHERE element = 'mod_magebridge_cart' AND type = 'module';" 2>/dev/null | tr -d '[:space:]')

  # Check if Cart module exists in modules table
  # shellcheck disable=2312
  CART_MODULE_EXISTS=$(joomla_mysql -sN -e "SELECT COUNT(*) FROM ${JOOMLA_DB_PREFIX}modules WHERE module = 'mod_magebridge_cart';" 2>/dev/null | tr -d '[:space:]')

  if [[ "${CART_MODULE_EXISTS}" == "0" ]] && [[ -n "${CART_MODULE_ID}" ]]; then
    # Get max ordering value for sidebar-right position (Joomla 5 Cassiopeia)
    # shellcheck disable=2312
    MAX_ORDERING=$(joomla_mysql -sN -e "SELECT COALESCE(MAX(ordering), 0) FROM ${JOOMLA_DB_PREFIX}modules WHERE position = 'sidebar-right' AND client_id = 0;" 2>/dev/null | tr -d '[:space:]')
    NEW_ORDERING=$((MAX_ORDERING + 1))

    # Create Cart module instance
    joomla_mysql -e "
      INSERT INTO ${JOOMLA_DB_PREFIX}modules
        (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
      VALUES
        (0, 'MageBridge: Cart', '', '', ${NEW_ORDERING}, 'sidebar-right', 0, NULL, NULL, NULL, 1, 'mod_magebridge_cart', 1, 1, '{\"layout\":\"native\",\"load_css\":\"1\",\"load_js\":\"1\",\"moduleclass_sfx\":\"\"}', 0, '*');
    " 2>/dev/null

    # Get the newly created module id
    # shellcheck disable=2312
    NEW_MODULE_ID=$(joomla_mysql -sN -e "SELECT LAST_INSERT_ID();" 2>/dev/null | tr -d '[:space:]')

    # Assign module to all menu items (menuid = 0)
    joomla_mysql -e "
      INSERT INTO ${JOOMLA_DB_PREFIX}modules_menu (moduleid, menuid) VALUES (${NEW_MODULE_ID}, 0);
    " 2>/dev/null

    echo "Cart module enabled and configured at sidebar-right"
  else
    # Module exists, update position, params, and ensure menu assignment
    joomla_mysql -e "
      -- Update module position to sidebar-right (Joomla 5 Cassiopeia)
      UPDATE ${JOOMLA_DB_PREFIX}modules
      SET published = 1,
          position = 'sidebar-right',
          params = '{\"layout\":\"native\",\"load_css\":\"1\",\"load_js\":\"1\",\"moduleclass_sfx\":\"\"}'
      WHERE module = 'mod_magebridge_cart';

      -- Ensure menu assignment exists (menuid = 0 means show on all pages)
      INSERT IGNORE INTO ${JOOMLA_DB_PREFIX}modules_menu (moduleid, menuid)
      SELECT id, 0
      FROM ${JOOMLA_DB_PREFIX}modules
      WHERE module = 'mod_magebridge_cart'
      AND id NOT IN (SELECT moduleid FROM ${JOOMLA_DB_PREFIX}modules_menu);
    " 2>/dev/null
    echo "Cart module already exists, updated to sidebar-right with menu assignment"
  fi

fi

echo "Fixing permissions ..."
dc_exec joomla chown -R www-data:www-data /var/www/html