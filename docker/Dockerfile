FROM php:8.4-fpm-alpine

ARG USER=najib
ARG GROUP=${USER}
ARG UID=1000
ARG GID=${UID}
ARG WORKDIR="/app"

ARG APP_ENV
RUN apk add --no-cache git

WORKDIR ${WORKDIR}

RUN addgroup --gid ${GID} ${GROUP} \
    && adduser --uid ${UID} --home ${WORKDIR} --ingroup ${GROUP} --no-create-home -D ${USER} \
    && chown -R ${USER}:${GROUP} ${WORKDIR}

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions zip bcmath @composer; \
    rm /usr/local/bin/install-php-extensions;

RUN apk add --no-cache \
        autoconf \
        gcc \
        g++ \
        make \
        libzip-dev \
        linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=172.29.0.1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


RUN docker-php-ext-install pdo pdo_mysql

USER ${USER}:${GROUP}

CMD ["sh", "/app/docker/entrypoint.sh"]
