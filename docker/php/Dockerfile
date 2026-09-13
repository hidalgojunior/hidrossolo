FROM php:8.3-fpm-alpine

# Dependências do sistema + build tools
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        mbstring \
        xml \
        zip \
        bcmath \
        opcache

# Instalar extensão Redis via PECL (precisa de $PHPIZE_DEPS)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuração do PHP para produção
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "date.timezone = America/Sao_Paulo" >> "$PHP_INI_DIR/php.ini" \
    && echo "default_charset = UTF-8" >> "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = 20M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 20M" >> "$PHP_INI_DIR/php.ini"

# Locale pt_BR
RUN apk add --no-cache icu-data-full icu-libs \
    && echo "pt_BR.UTF-8 UTF-8" >> /etc/locale.gen 2>/dev/null || true

WORKDIR /var/www/html

# Otimizações OPCache
RUN echo "opcache.memory_consumption=128" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=8" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=10000" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=2" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.fast_shutdown=1" >> $PHP_INI_DIR/conf.d/opcache.ini

CMD ["php-fpm"]
