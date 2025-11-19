FROM php:8.3-apache

# Habilita mod_rewrite (necessário para URLs amigáveis)
RUN a2enmod rewrite

# Instala extensões importantes para Laravel e PHP moderno
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libicu-dev \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    mbstring \
    intl \
    gd \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer (imagem oficial como fonte)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define DocumentRoot para /var/www/html/public (estilo Laravel)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN mkdir -p /var/www/html/public && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/docker-php.conf

WORKDIR /var/www/html

# Cria usuário não-root com UID e GID do host
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} appuser && \
    useradd -u ${UID} -g appuser -m -s /bin/bash appuser

# Permissões
RUN chown -R appuser:appuser /var/www

# Configura Apache para rodar com o novo usuário
RUN sed -i 's/User www-data/User appuser/g' /etc/apache2/apache2.conf && \
    sed -i 's/Group www-data/Group appuser/g' /etc/apache2/apache2.conf

USER appuser

EXPOSE 80
