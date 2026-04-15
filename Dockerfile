FROM php:7.4.30-apache
WORKDIR /var/www/html
RUN apt-get update

RUN apt-get install -y libzip-dev libssl-dev zip
RUN apt-get install -y \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev

# Instala las dependencias necesarias, incluyendo Python
RUN apt-get update && \
  apt-get install -y \
  libssl-dev \
  pkg-config \
  cmake \
  git \
  build-essential \
  python

# Clona e instala libbson y libmongoc con soporte para SSL
RUN git clone https://github.com/mongodb/mongo-c-driver.git && \
  cd mongo-c-driver && \
  git checkout 1.17.2 && \
  mkdir cmake-build && \
  cd cmake-build && \
  cmake -DENABLE_AUTOMATIC_INIT_AND_CLEANUP=OFF -DENABLE_SSL=OPENSSL .. && \
  make && \
  make install

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    libssl-dev \
    pkg-config \
    cmake \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring zip

# Install MongoDB
RUN pecl install mongodb

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip
RUN docker-php-ext-enable mongodb pdo pdo_mysql gd zip


RUN echo "memory_limit=1024M" > /usr/local/etc/php/conf.d/custom.ini
RUN echo "upload_max_filesize=1024M" >> /usr/local/etc/php/conf.d/custom.ini
RUN echo "post_max_size=1024M" >> /usr/local/etc/php/conf.d/custom.ini


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY ./composer.json ./
RUN composer update
#COPY ./config/docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN a2enmod headers
RUN rm -rf /tmp/*
