FROM quay.io/nadyita/alpine:3.16

LABEL maintainer="nadyita@hodorraid.org" \
      description="self-sustaining docker image to run Redactus websocket server"

ENTRYPOINT ["/sbin/tini", "-g", "--"]

CMD ["/usr/bin/php", "-dopcache.enable_cli=1", "-dopcache.jit_buffer_size=128M", "-dopcache.jit=1235", "/server/src/main.php"]

RUN apk --no-cache add \
    php8-cli \
    php8-phar \
    php8-mbstring \
    php8-ctype \
    php8-bcmath \
    php8-json \
    php8-posix \
    php8-gmp \
    php8-openssl \
    php8-zip \
    php8-opcache \
    tini \
    && \
    adduser -h /server -s /bin/false -D -H redactus

COPY --chown=redactus:redactus . /server

RUN wget -O /usr/bin/composer https://getcomposer.org/composer-2.phar && \
    apk --no-cache add \
        sudo \
    && \
    cd /server && \
    sudo -u redactus php8 /usr/bin/composer install --no-dev --no-interaction --no-progress -q && \
    sudo -u redactus php8 /usr/bin/composer dumpautoload --no-dev --optimize --no-interaction 2>&1 | grep -v "/20[0-9]\{12\}_.*autoload" && \
    sudo -u redactus php8 /usr/bin/composer clear-cache -q && \
    rm -f /usr/bin/composer && \
    apk del --no-cache sudo

USER redactus

WORKDIR /server
