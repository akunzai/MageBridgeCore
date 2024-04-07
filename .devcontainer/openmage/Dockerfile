# https://github.com/colinmollenhour/docker-openmage/blob/main/8.2/apache/Dockerfile
# https://github.com/OpenMage/magento-lts/tree/main/dev/openmage
ARG PHP_VERSION=8.2
ARG OPENMAGE_VERSION=20.6.0

FROM alpine as unzipper
ARG OPENMAGE_VERSION
RUN set -eux; \
    apk add curl unzip; \
    mkdir -p /usr/src/openmage; \
    curl -Lo /tmp/openmage.zip https://github.com/OpenMage/magento-lts/releases/download/v${OPENMAGE_VERSION}/openmage-v${OPENMAGE_VERSION}.zip; \
    unzip /tmp/openmage.zip -d /usr/src/openmage; \
    rm /tmp/openmage.zip;

# https://hub.docker.com/_/php
FROM php:${PHP_VERSION}-apache-bookworm

ENV LANG=en_US.UTF-8

# install MySQL client
RUN apt-get update && \
    apt-get install -y --no-install-recommends default-mysql-client && \
    apt-get clean -y && rm -rf /var/lib/apt/lists/*

# install pickle
# https://github.com/FriendsOfPHP/pickle
RUN set -eux; \
    curl -Lo /usr/local/bin/pickle https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar && \
    chmod +x /usr/local/bin/pickle;

# install the PHP extensions we need
RUN set -eux; \
    # https://github.com/colinmollenhour/modman
    curl -fSL -o /usr/local/bin/modman https://raw.githubusercontent.com/colinmollenhour/modman/master/modman && \
    chmod +x /usr/local/bin/modman; \
    # https://github.com/netz98/n98-magerun
    curl -fSL -o /usr/local/bin/n98-magerun https://files.magerun.net/n98-magerun.phar && \
    chmod +x /usr/local/bin/n98-magerun; \
    # install the PHP extensions we need
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends default-mysql-client \
    libfreetype6-dev libicu-dev libjpeg62-turbo-dev libonig-dev \
    libpng-dev libxml2-dev libxslt1-dev libzip-dev libwebp-dev \
    ${PHP_EXTRA_BUILD_DEPS:-}; \
    # https://www.php.net/manual/en/image.installation.php
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j$(nproc) opcache \
    intl gd mysqli pcntl pdo_mysql soap xsl zip; \
    pickle install --no-interaction apcu; \
    pickle install --no-interaction redis; \
    pickle install --no-interaction xdebug; \
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
    rm -rf /var/lib/apt/lists/*;

# set up Apache2
RUN set -eux; \
    # enable Apache2 modules
    a2enmod rewrite expires include deflate remoteip headers; \
    # disable Apache2 server signature
    echo 'ServerSignature Off' >> /etc/apache2/apache2.conf; \
    echo 'ServerTokens Prod' >> /etc/apache2/apache2.conf; \
    # enable support for TLS termination
    echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on' >> /etc/apache2/apache2.conf;

# install composer
# https://hub.docker.com/_/composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# install modman
# https://github.com/colinmollenhour/modman
RUN set -eux; \
    curl -fSL -o /usr/local/bin/modman https://raw.githubusercontent.com/colinmollenhour/modman/master/modman && \
    chmod +x /usr/local/bin/modman;

# install n98-magerun
# https://github.com/netz98/n98-magerun
RUN set -eux; \
    curl -fSL -o /usr/local/bin/n98-magerun https://files.magerun.net/n98-magerun.phar && \
    chmod +x /usr/local/bin/n98-magerun;

# set up OpenMage
COPY --chmod=755 ./docker-entrypoint.sh /
COPY --from=unzipper --chown=www-data:www-data /usr/src/openmage /usr/src/openmage
RUN set -eux; \
    chmod +x /docker-entrypoint.sh; \
    chown -R www-data:www-data /usr/src/openmage; \
    chmod -R g+w /usr/src/openmage;

ENTRYPOINT [ "/docker-entrypoint.sh" ]
CMD [ "apache2-foreground" ]