# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && apt-get clean

# Instala la extensión de MongoDB para PHP
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Instala Node.js (versión estable)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Instala Gulp CLI globalmente
RUN npm install -g gulp-cli

# Instala Composer copiándolo desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de configuración del proyecto
COPY package*.json ./  
COPY composer.json ./  

# Instala las dependencias del proyecto (Node.js)
RUN npm install

# Instala las dependencias de PHP
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Copia el resto de los archivos del proyecto
COPY . .

# Configura permisos y habilita mod_rewrite en Apache
RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

# Expone el puerto 80
EXPOSE 80
