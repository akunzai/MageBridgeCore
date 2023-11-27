#!/usr/bin/env bash
# https://github.com/OpenMage/magento-lts/blob/main/dev/openmage/install.sh

[ -f .env ] && source .env

docker compose exec openmage test -f app/etc/local.xml
if [ "$?" -eq "0" ]; then
  echo "OpenMage already installed!"
  exit 0
fi

echo "Checking database ..."
for i in $(seq 1 20); do
  docker compose exec mysql sh -c "mysql -uroot -p${MYSQL_ROOT_PASSWORD:-secret} -e 'show databases;' 2>/dev/null" | grep -qF 'openmage' && break
  sleep 1
done

INSTALL_SAMPLE_DATA="${INSTALL_SAMPLE_DATA:-true}"

if [ "${INSTALL_SAMPLE_DATA}" = "true" ]; then
  SAMPLE_DATA_DIR="${SAMPLE_DATA_DIR:-magento-sample-data-1.9.2.4}"
  SAMPLE_DATA_SQL="${SAMPLE_DATA_SQL:-magento_sample_data_for_1.9.2.4.sql}"
  docker compose exec openmage test -d /tmp/${SAMPLE_DATA_DIR}
  if [ "$?" -ne "0" ]; then
    echo "Downloading Sample Data ..."
    docker compose exec --user www-data openmage curl -Lo /tmp/sample_data.tgz "${SAMPLE_DATA_URL:-https://github.com/Vinai/compressed-magento-sample-data/raw/master/compressed-magento-sample-data-1.9.2.4.tgz}"

    echo "Uncompressing Sample Data ..."
    docker compose exec --user www-data openmage tar zxf /tmp/sample_data.tgz -C /tmp/

    echo "Copying Sample Data into the OpenMage directory..."
    docker compose exec --user www-data openmage sh -c "cp -r /tmp/${SAMPLE_DATA_DIR}/* /var/www/html/"

    echo "Importing Sample Data into the database..."
    docker compose exec --user www-data openmage sh -c "mysql -h ${OPENMAGE_DB_HOST:-mysql} -u${OPENMAGE_DB_USER:-root} -p${OPENMAGE_DB_PASSWORD:-${MYSQL_ROOT_PASSWORD:-secret}} ${OPENMAGE_DB_NAME:-openmage} < /tmp/${SAMPLE_DATA_DIR}/${SAMPLE_DATA_SQL}"

    echo "Cleaning up ..."
    docker compose exec --user www-data openmage rm /tmp/sample_data.tgz
    docker compose exec --user www-data openmage rm "/var/www/html/${SAMPLE_DATA_SQL}"
  fi
fi

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
  --url "$(echo ${BASE_URL:-"https://store.dev.local"} | sed -e 's/^https:/http:/')" \
  --secure_base_url "$(echo ${BASE_URL:-"https://store.dev.local"} | sed -e 's/^http:/https:/')" \
  --skip_url_validation \
  --license_agreement_accepted yes \
  --use_rewrites yes \
  --use_secure yes \
  --use_secure_admin yes \
  --encryption_key "${ENCRYPTION_KEY:-}"

echo "Installing MageBridge module ..."
docker compose exec --user www-data openmage modman deploy MageBridge

echo "Configuring MageBridge module ..."
docker compose exec --user www-data openmage n98-magerun config:set 'magebridge/joomla/autoadd_allowed_ips' 0

echo "Disable auto-redirect to base URL ..."
docker compose exec --user www-data openmage n98-magerun config:set 'web/url/redirect_to_base' 0

echo "Clear catalog product URL suffix ..."
docker compose exec --user www-data openmage n98-magerun config:set 'catalog/seo/category_url_suffix' ''

echo "Clear catalog product URL suffix ..."
docker compose exec --user www-data openmage n98-magerun config:set 'catalog/seo/product_url_suffix' ''

echo "Refreshing cache ..."
docker compose exec --user www-data openmage n98-magerun cache:flush

echo "Fixing permissions ..."
docker compose exec openmage chown -R www-data:www-data /var/www/html