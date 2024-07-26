FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    wget \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libaio1 libaio-dev \
    libxml2 libxml2-dev \
    supervisor \
    librabbitmq-dev \
    libssl-dev \
    iputils-ping \
    ghostscript

RUN pecl install amqp && pecl install xdebug
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql \
&& docker-php-ext-install mbstring \
&& docker-php-ext-install zip \
&& docker-php-ext-install exif \
&& docker-php-ext-install pcntl \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install gd \
&& docker-php-ext-configure intl \
&& docker-php-ext-install intl \
&& docker-php-ext-install soap \
&& pecl install -o -f redis \
&& rm -rf /tmp/pear \
&& docker-php-ext-enable redis \
&& docker-php-ext-enable amqp \
&& docker-php-ext-enable xdebug

COPY ./.docker/supervisord.conf /etc/supervisor/supervisord.conf

#USER docker
COPY ./ /app

WORKDIR /app

RUN mkdir /app/vendor || true
RUN mkdir /app/var || true
RUN touch /app/var/BUILD

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
