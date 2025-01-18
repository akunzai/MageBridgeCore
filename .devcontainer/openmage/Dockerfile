# https://github.com/colinmollenhour/docker-openmage/blob/main/8.2/apache/Dockerfile
# https://github.com/OpenMage/magento-lts/tree/main/dev/openmage
ARG PHP_VERSION=8.3
ARG PHP_EXTRA_BUILD_DEPS=""
ARG OPENMAGE_VERSION=20.12.2

# https://github.com/OpenMage/magento-lts/blob/main/.github/workflows/release.yml
FROM composer AS builder
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

# https://hub.docker.com/_/php
FROM php:${PHP_VERSION}-apache

ENV LANG=en_US.UTF-8

# hadolint ignore=SC2086
RUN --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    --mount=type=cache,target=/var/cache/apt,sharing=locked \
    set -eux; \
    apt-get update; \
    # install the system tools we need
    apt-get install -y --no-install-recommends mariadb-client; \
    # install the PHP extensions we need
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
    curl -Lo /usr/local/bin/pickle https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar && \
    chmod +x /usr/local/bin/pickle; \
    pickle install --no-interaction apcu-stable; \
    pickle install --no-interaction redis-stable; \
    pickle install --no-interaction xdebug-stable; \
    docker-php-ext-enable apcu opcache redis xdebug; \
    # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
    apt-mark auto '.*' > /dev/null; \
    [ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
    find /usr/local -type f -executable -exec ldd '{}' ';' \
    | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); print so }' \
    | sort -u \
    | xargs -r dpkg-query --search \
    | cut -d: -f1 \
    | sort -u \
    | xargs -r apt-mark manual; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
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
# https://hub.docker.com/_/composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

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