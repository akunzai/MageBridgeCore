#!/usr/bin/env bash
# https://github.com/OpenMage/magento-lts/blob/main/dev/openmage/install.sh

# shellcheck disable=1091
[[ -f .env ]] && source .env

echo "Checking database ..."
for _ in $(seq 1 20); do
  docker compose exec mysql sh -c "mysql -uroot -p${MYSQL_ROOT_PASSWORD:-secret} -e 'show databases;' 2>/dev/null" | grep -qF 'openmage' && break
  sleep 1
done

if ! docker compose exec openmage test -f app/etc/local.xml; then
  echo "Installing OpenMage ..."
  docker compose exec --user www-data openmage php install.php \
    --admin_firstname OpenMage  \
    --admin_lastname Admin \
    --admin_username "${ADMIN_USERNAME:-admin}" \
    --admin_password "${ADMIN_PASSWORD:-ChangeTheP@ssw0rd}" \
    --admin_email "${ADMIN_EMAIL:-admin@example.com}" \
    --db_host "${OPENMAGE_DB_HOST:-mysql}" \
    --db_name "${OPENMAGE_DB_NAME:-openmage}" \
    --db_user "${OPENMAGE_DB_USER:-root}" \
    --db_pass "${OPENMAGE_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}}" \
    --locale "${LOCALE:-en_US}" \
    --timezone "${TIMEZONE:-America/New_York}" \
    --default_currency "${CURRENCY:-USD}" \
    --url "${HTTP_BASE_URL:-http://store.dev.local}" \
    --secure_base_url "${HTTPS_BASE_URL:-https://store.dev.local}" \
    --skip_url_validation \
    --license_agreement_accepted yes \
    --use_rewrites yes \
    --use_secure yes \
    --use_secure_admin yes \
    --encryption_key "${ENCRYPTION_KEY:-}"

  INSTALL_SAMPLE_DATA="${INSTALL_SAMPLE_DATA:-true}"
  SAMPLE_DATA_DIR="${SAMPLE_DATA_DIR:-magento-sample-data-1.9.2.4}"
  SAMPLE_DATA_SQL="${SAMPLE_DATA_SQL:-magento_sample_data_for_1.9.2.4.sql}"

  if [[ "${INSTALL_SAMPLE_DATA}" = "true" ]] && ! docker compose exec openmage test -f "/var/www/html/${SAMPLE_DATA_SQL}"; then
    echo "Downloading Sample Data ..."
    docker compose exec --user www-data openmage curl -Lo /tmp/sample_data.tgz "${SAMPLE_DATA_URL:-https://github.com/Vinai/compressed-magento-sample-data/raw/master/compressed-magento-sample-data-1.9.2.4.tgz}"

    echo "Uncompressing Sample Data ..."
    docker compose exec --user www-data openmage tar zxf /tmp/sample_data.tgz -C /tmp/

    echo "Copying Sample Data files into the OpenMage directory ..."
    docker compose exec --user www-data openmage sh -c "cp -r /tmp/${SAMPLE_DATA_DIR}/* /var/www/html/"

    echo "Importing Sample Data into the database ..."
    # Use mysql CLI with --skip-ssl to bypass self-signed certificate verification
    docker compose exec --user www-data openmage sh -c "mysql --skip-ssl -h ${OPENMAGE_DB_HOST:-mysql} -u${OPENMAGE_DB_USER:-root} -p${OPENMAGE_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${OPENMAGE_DB_NAME:-openmage} < /var/www/html/${SAMPLE_DATA_SQL}"

    echo "Cleaning up Sample Data files ..."
    docker compose exec --user www-data openmage rm /tmp/sample_data.tgz
  fi
fi

echo "Installing MageBridge module ..."
docker compose exec --user www-data openmage modman deploy MageBridge

echo "Configuring MageBridge module ..."
docker compose exec --user www-data openmage n98-magerun config:set 'magebridge/joomla/autoadd_allowed_ips' 0

echo "Disable auto-redirect to base URL ..."
docker compose exec --user www-data openmage n98-magerun config:set 'web/url/redirect_to_base' 0

echo "Clear catalog URL suffix ..."
docker compose exec --user www-data openmage n98-magerun config:set 'catalog/seo/category_url_suffix' ''

echo "Clear product URL suffix ..."
docker compose exec --user www-data openmage n98-magerun config:set 'catalog/seo/product_url_suffix' ''

echo "Refreshing cache ..."
docker compose exec --user www-data openmage n98-magerun cache:flush

echo "Fixing permissions ..."
docker compose exec openmage chown -R www-data:www-data /var/www/html > /dev/null 2>&1