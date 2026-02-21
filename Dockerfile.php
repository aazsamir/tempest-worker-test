FROM php:8.5-cli-trixie
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install intl zip
ENV FRANKENPHP_WORKER=0
WORKDIR /app
COPY . /app
RUN php tempest discovery:generate
RUN php tempest migrate-and-seed
ENTRYPOINT [ "php", "-S", "0.0.0.0:8000", "-t", "public" ]