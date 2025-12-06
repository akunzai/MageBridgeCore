#!/usr/bin/env bash
# https://github.com/OpenMage/magento-lts/blob/main/dev/openmage/install.sh

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

ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"
ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-ChangeTheP@ssw0rd}"
OPENMAGE_DB_HOST="${OPENMAGE_DB_HOST:-mysql}"
OPENMAGE_DB_NAME="${OPENMAGE_DB_NAME:-openmage}"
OPENMAGE_DB_USER="${OPENMAGE_DB_USER:-root}"
OPENMAGE_DB_PASSWORD="${OPENMAGE_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}}"
OPENMAGE_COOKIE_DOMAIN="${OPENMAGE_COOKIE_DOMAIN:-}"
LOCALE="${LOCALE:-en_US}"
TIMEZONE="${TIMEZONE:-America/New_York}"
CURRENCY="${CURRENCY:-USD}"
HTTP_BASE_URL="${HTTP_BASE_URL:-https://store.dev.local}"
HTTPS_BASE_URL="${HTTPS_BASE_URL:-https://store.dev.local}"
ENCRYPTION_KEY="${ENCRYPTION_KEY:-}"
INSTALL_SAMPLE_DATA="${INSTALL_SAMPLE_DATA:-true}"
SAMPLE_DATA_DIR="${SAMPLE_DATA_DIR:-magento-sample-data-1.9.2.4}"
SAMPLE_DATA_SQL="${SAMPLE_DATA_SQL:-magento_sample_data_for_1.9.2.4.sql}"
SAMPLE_DATA_TGZ="${SAMPLE_DATA_TGZ:-sample_data_1.9.2.4.tgz}"
SAMPLE_DATA_URL="${SAMPLE_DATA_URL:-https://github.com/Vinai/compressed-magento-sample-data/raw/master/compressed-magento-sample-data-1.9.2.4.tgz}"
CACHE_DIR="/var/www/html/.modman/MageBridge/.devcontainer/openmage/.cache"

echo "Checking database ..."
for _ in $(seq 1 20); do
  # Always use -T for MySQL operations (no TTY needed)
  # shellcheck disable=SC2086
  docker compose exec -T -e MYSQL_PWD="${MYSQL_ROOT_PASSWORD:-secret}" mysql mysql -uroot -e 'show databases;' 2>/dev/null | grep -qF 'openmage' && break
  sleep 1
done

if ! dc_exec openmage test -f app/etc/local.xml; then

  # Sample Data must be imported BEFORE install.php runs,
  # otherwise the SQL import will overwrite the admin user created by install.php
  if [[ "${INSTALL_SAMPLE_DATA}" = "true" ]] && ! dc_exec openmage test -f "/var/www/html/${SAMPLE_DATA_SQL}"; then
    # Check if sample data exists in cache (mounted from host)
    if dc_exec openmage test -f "${CACHE_DIR}/${SAMPLE_DATA_TGZ}"; then
      echo "Using cached Sample Data ..."
      dc_exec --user www-data openmage cp "${CACHE_DIR}/${SAMPLE_DATA_TGZ}" /tmp/sample_data.tgz
    else
      echo "Downloading Sample Data ..."
      dc_exec --user www-data openmage curl -Lo /tmp/sample_data.tgz "${SAMPLE_DATA_URL}"

      # Save to cache for next time (use root to avoid permission issues)
      echo "Caching Sample Data for future use ..."
      dc_exec openmage mkdir -p "${CACHE_DIR}"
      dc_exec openmage cp /tmp/sample_data.tgz "${CACHE_DIR}/${SAMPLE_DATA_TGZ}"
      dc_exec openmage chmod 644 "${CACHE_DIR}/${SAMPLE_DATA_TGZ}"
    fi

    echo "Uncompressing Sample Data ..."
    dc_exec --user www-data openmage tar zxf /tmp/sample_data.tgz -C /tmp/

    echo "Copying Sample Data files into the OpenMage directory ..."
    dc_exec --user www-data openmage sh -c "cp -r /tmp/${SAMPLE_DATA_DIR}/* /var/www/html/"

    echo "Importing Sample Data into the database ..."
    # Always use -T for MySQL operations (no TTY needed)
    # shellcheck disable=SC2086
    docker compose exec -T --user www-data openmage sh -c "MYSQL_PWD=${OPENMAGE_DB_PASSWORD} mysql --skip-ssl -h ${OPENMAGE_DB_HOST} -u${OPENMAGE_DB_USER} ${OPENMAGE_DB_NAME} < /var/www/html/${SAMPLE_DATA_SQL}"

    echo "Cleaning up Sample Data files ..."
    dc_exec --user www-data openmage rm /tmp/sample_data.tgz
  fi

  echo "Installing OpenMage ..."
  dc_exec --user www-data openmage php install.php \
    --admin_firstname OpenMage  \
    --admin_lastname Admin \
    --admin_username "${ADMIN_USERNAME}" \
    --admin_password "${ADMIN_PASSWORD}" \
    --admin_email "${ADMIN_EMAIL}" \
    --db_host "${OPENMAGE_DB_HOST}" \
    --db_name "${OPENMAGE_DB_NAME}" \
    --db_user "${OPENMAGE_DB_USER}" \
    --db_pass "${OPENMAGE_DB_PASSWORD}" \
    --locale "${LOCALE}" \
    --timezone "${TIMEZONE}" \
    --default_currency "${CURRENCY}" \
    --url "${HTTP_BASE_URL}" \
    --secure_base_url "${HTTPS_BASE_URL}" \
    --skip_url_validation \
    --license_agreement_accepted yes \
    --use_rewrites yes \
    --use_secure yes \
    --use_secure_admin yes \
    --encryption_key "${ENCRYPTION_KEY}"
fi

echo "Installing MageBridge module ..."
dc_exec --user www-data openmage modman deploy MageBridge

echo "Clearing cache to load MageBridge API definitions ..."
dc_exec --user www-data openmage n98-magerun cache:flush
# Also clear the var/cache directory to ensure API WSDL is regenerated
dc_exec --user www-data openmage rm -rf var/cache/* var/wsdlcache/* 2>/dev/null || true

echo "Creating MageBridge API Role and User ..."
# shellcheck disable=SC2016
dc_exec --user www-data openmage php -r '
require_once "app/Mage.php";
Mage::app("admin");

$roleName = "MageBridge";
$username = "magebridge_api";
$apiKey = "ChangeTheAp1K3y";

// Check if API role exists
$roleCollection = Mage::getModel("api/roles")->getCollection()
    ->addFieldToFilter("role_name", $roleName)
    ->addFieldToFilter("role_type", "G");
$role = $roleCollection->getFirstItem();

if (!$role->getId()) {
    echo "Creating API Role...\n";
    $role = Mage::getModel("api/roles");
    $role->setName($roleName)
         ->setRoleType("G")
         ->save();

    $rule = Mage::getModel("api/rules");
    $rule->setRoleId($role->getId())
         ->setResourceId("all")
         ->setRoleType("G")
         ->setApiPermission("allow")
         ->save();
} else {
    echo "API Role already exists\n";
}

// Check if API user exists
$user = Mage::getModel("api/user")->load($username, "username");
if (!$user->getId()) {
    echo "Creating API User...\n";
    $user = Mage::getModel("api/user");
    $user->setUsername($username)
         ->setFirstname("Mage")
         ->setLastname("Bridge")
         ->setEmail("magebridge@example.com")
         ->setApiKey($apiKey)
         ->setIsActive(1)
         ->save();
} else {
    echo "API User already exists\n";
}

// Always ensure the user is assigned to the correct role
echo "Assigning API User to MageBridge role...\n";
$user->setRoleIds(array($role->getId()))
     ->setRoleUserId($user->getId())
     ->saveRelations();
'

echo "Configuring MageBridge module ..."
dc_exec --user www-data openmage n98-magerun config:set 'magebridge/joomla/autoadd_allowed_ips' 0

echo "Disable auto-redirect to base URL ..."
dc_exec --user www-data openmage n98-magerun config:set 'web/url/redirect_to_base' 0

echo "Clear catalog URL suffix ..."
dc_exec --user www-data openmage n98-magerun config:set 'catalog/seo/category_url_suffix' ''

echo "Clear product URL suffix ..."
dc_exec --user www-data openmage n98-magerun config:set 'catalog/seo/product_url_suffix' ''

if [[ -n "${OPENMAGE_COOKIE_DOMAIN}" ]]; then
  echo "Configuring cookie domain ..."
  dc_exec --user www-data openmage n98-magerun config:set 'web/cookie/cookie_domain' "${OPENMAGE_COOKIE_DOMAIN}"
fi

echo "Refreshing cache ..."
dc_exec --user www-data openmage n98-magerun cache:flush

echo "Fixing permissions ..."
dc_exec openmage chown -R www-data:www-data /var/www/html > /dev/null 2>&1