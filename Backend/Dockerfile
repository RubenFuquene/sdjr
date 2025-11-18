# Etapa 1: Imagen base con PHP 8.3 y Apache
# Se usa una versión específica para asegurar compilaciones consistentes.
FROM php:8.3-apache

# Argumentos para configuración de usuario (opcional, para desarrollo)
ARG UID
ARG GID

# 1. Configuración del entorno y directorio de trabajo
COPY . /var/www/html/
WORKDIR /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# 2. Instalación de dependencias del sistema y extensiones de PHP
#    - Se agrupan en una sola capa RUN para optimizar el tamaño de la imagen.
#    - Se limpian los cachés de apt en la misma capa.
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    # Dependencias para extensiones PHP
    libicu-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libwebp-dev \
    libpq-dev \
    libxslt-dev \
    # Utilidades comunes
    git \
    unzip \
    && \
    # Configurar e instalar extensiones de PHP para Laravel 11
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    xsl \
    zip \
    && \
    # Limpiar caché de apt para reducir el tamaño de la imagen
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# 2. Apache configs + document root.
RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    a2enmod rewrite headers

# 3. Start with base PHP config, then add extensions.
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# 4. Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Permisos para Laravel
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

## Update composer
RUN composer install