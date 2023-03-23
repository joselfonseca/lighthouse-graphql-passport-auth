ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        oniguruma-dev \
    && apk add --no-cache \
        shadow \
    && apk del -f .build-deps \
    && curl -s https://getcomposer.org/installer | \
        php -- --install-dir=/usr/local/bin/ --filename=composer

ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

RUN groupmod -o -g ${PGID} nobody && \
    usermod -o -u ${PUID} -g nobody nobody && \
    apk del shadow

RUN mkdir /.config && \
    chown -R nobody.nobody /.config

RUN mkdir /.composer && \
    chown -R nobody:nobody /.composer

USER nobody

WORKDIR /var/www/html
