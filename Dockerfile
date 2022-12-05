FROM quay.io/nadyita/alpine:3.17

LABEL maintainer="nadyita@hodorraid.org" \
      description="self-sustaining docker image to run Redactus websocket server"

ENTRYPOINT ["/sbin/tini", "-g", "--"]

CMD ["/usr/bin/php", "-dopcache.enable_cli=1", "-dopcache.jit_buffer_size=128M", "-dopcache.jit=1235", "/server/src/main.php"]

RUN apk --no-cache add \
    php81-cli \
    php81-phar \
    php81-mbstring \
    php81-ctype \
    php81-bcmath \
    php81-json \
    php81-posix \
    php81-gmp \
    php81-openssl \
    php81-zip \
    php81-opcache \
    tini \
    && \
    adduser -h /server -s /bin/false -D -H redactus

COPY --chown=redactus:redactus . /server

RUN wget -O /usr/bin/composer https://getcomposer.org/composer-2.phar && \
    apk --no-cache add \
        sudo \
    && \
    cd /server && \
    sudo -u redactus php81 /usr/bin/composer install --no-dev --no-interaction --no-progress -q && \
    sudo -u redactus php81 /usr/bin/composer dumpautoload --no-dev --optimize --no-interaction 2>&1 | grep -v "/20[0-9]\{12\}_.*autoload" && \
    sudo -u redactus php81 /usr/bin/composer clear-cache -q && \
    rm -f /usr/bin/composer && \
    apk del --no-cache sudo

USER redactus

WORKDIR /server
