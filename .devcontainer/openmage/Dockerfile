# https://github.com/colinmollenhour/docker-openmage/blob/main/8.2/apache/Dockerfile
# https://github.com/OpenMage/magento-lts/tree/main/dev/openmage
ARG PHP_VERSION=8.4
ARG PHP_EXTRA_BUILD_DEPS=""
ARG OPENMAGE_VERSION=20.18.0

# Pin major tag for composer builder
# https://github.com/OpenMage/magento-lts/blob/main/.github/workflows/release.yml
FROM composer:2 AS builder
ARG OPENMAGE_VERSION
# hadolint ignore=DL3003
RUN set -eux; \
    mkdir -p /usr/src/openmage; \
    curl -Lo /tmp/openmage.tgz https://github.com/OpenMage/magento-lts/archive/refs/tags/v${OPENMAGE_VERSION}.tar.gz; \
    tar zxf /tmp/openmage.tgz -C /usr/src/openmage --strip-components=1; \
    rm /tmp/openmage.tgz; \
    cd /usr/src/openmage; \
    composer install --prefer-dist --no-progress --ignore-platform-req=ext-* --no-dev; \
    composer dump-autoload --optimize;

# -----------------------------------------------------------------------------
# php-ext: expensive compile layer (docker-php-ext-install + PECL).
# Cache key depends on PHP_VERSION / PHP_EXTRA_BUILD_DEPS / this stage only.
# -----------------------------------------------------------------------------
# https://hub.docker.com/_/php
FROM php:${PHP_VERSION}-apache AS php-ext

ARG PHP_EXTRA_BUILD_DEPS

ENV LANG=en_US.UTF-8

# hadolint ignore=SC2086
RUN --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    --mount=type=cache,target=/var/cache/apt,sharing=locked \
    set -eux; \
    apt-get update; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get install -y --no-install-recommends \
    libonig-dev libssl-dev \
    libfreetype6-dev libicu-dev \
    libjpeg62-turbo-dev libpng-dev \
    libxml2-dev libxslt1-dev \
    libzip-dev libwebp-dev \
    ${PHP_EXTRA_BUILD_DEPS:-}; \
    # https://www.php.net/manual/en/image.installation.php
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j$(nproc) ftp \
    gd intl mysqli opcache pcntl pdo_mysql soap xsl zip; \
    # Pin pickle release so this layer is reproducible (not floating "latest").
    curl -fsSL -o /usr/local/bin/pickle \
      https://github.com/FriendsOfPHP/pickle/releases/download/v0.7.11/pickle.phar; \
    chmod +x /usr/local/bin/pickle; \
    pickle install --no-interaction apcu-stable; \
    pickle install --no-interaction redis-stable; \
    pickle install --no-interaction xdebug-stable; \
    docker-php-ext-enable apcu opcache redis xdebug; \
    # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
    apt-mark auto '.*' > /dev/null; \
    [ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
    find /usr/local -type f -executable -exec ldd '{}' ';' 2>/dev/null \
    | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); print so }' \
    | sort -u \
    | xargs -r dpkg-query --search \
    | grep -v -i '^diversion' \
    | cut -d: -f1 \
    | sort -u \
    | xargs -r apt-mark manual; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /tmp/*;

# -----------------------------------------------------------------------------
# Runtime image: cheap layers on top of php-ext
# -----------------------------------------------------------------------------
FROM php-ext

# hadolint ignore=DL3008
RUN --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    --mount=type=cache,target=/var/cache/apt,sharing=locked \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends mariadb-client unzip ca-certificates; \
    rm -rf /tmp/*;

# set up Apache2
RUN set -eux; \
    # change listening port to 8080
    sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf; \
    sed -i 's/*:80/*:8080/' /etc/apache2/sites-available/000-default.conf; \
    # enable Apache2 modules
    a2enmod rewrite expires include deflate remoteip headers; \
    { \
        # disable Apache2 server signature
        echo 'ServerSignature Off'; \
        echo 'ServerTokens Prod'; \
        # enable support for TLS termination
        echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on'; \
    } >> /etc/apache2/apache2.conf;

# install composer
# Pin major tag (not :latest) so COPY --from digest churn is limited.
# https://hub.docker.com/_/composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# install modman
# https://github.com/colinmollenhour/modman
ADD --chmod=755 https://raw.githubusercontent.com/colinmollenhour/modman/master/modman /usr/local/bin/modman

# install n98-magerun
# https://github.com/netz98/n98-magerun
ADD --chmod=755 https://files.magerun.net/n98-magerun.phar /usr/local/bin/n98-magerun

# set up OpenMage
COPY --chmod=755 ./docker-entrypoint.sh /
COPY --from=builder --chown=www-data:www-data /usr/src/openmage /usr/src/openmage
RUN set -eux; \
    chmod +x /docker-entrypoint.sh; \
    chown -R www-data:www-data /usr/src/openmage; \
    chmod -R g+w /usr/src/openmage;

EXPOSE 8080
ENTRYPOINT [ "/docker-entrypoint.sh" ]
CMD [ "apache2-foreground" ]