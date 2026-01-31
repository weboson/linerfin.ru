FROM php:8.0-fpm

# Устанавливаем рабочую директорию 
WORKDIR /var/www/

# Копируем composer.lock и composer.json
COPY composer.lock composer.json /var/www/

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
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
    libpq-dev \
    zlib1g-dev \
    libicu-dev \
    g++ \
    libonig-dev \
    libzip-dev \
    # Для wkhtmltopdf и зависимости (для скачивания счетов (pdf))
    wget \
    xvfb \
    xfonts-75dpi \
    xfonts-base \
    libxrender1 \
    libfontconfig1 \
    fontconfig \
    libjpeg62-turbo \
    # Для ImageMagick (удаление фона с печатей и подписей)
    imagemagick \
    libmagickwand-dev \
    libmagickcore-dev

# Очищаем кэш
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Устанавливаем Imagick (удаление фона с печатей и подписей)
RUN pecl install imagick && docker-php-ext-enable imagick

# Устанавливаем wkhtmltopdf: (программа для скачивания счетов (pdf))
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb
RUN dpkg -i wkhtmltox_0.12.6-1.buster_amd64.deb || true
RUN apt-get -f install -y

# Загружаем актуальную версию Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Создаём пользователя и группу www для приложения Laravel
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Копируем содержимое текущего каталога в рабочую директорию
COPY . /var/www
COPY --chown=www:www . /var/www

# Меняем пользователя на www
USER www
#RUN chmod -R 775 /var/www/linercrm.ru/*
#RUN chmod -R 775 /var/www/lirefin.ru/*

# В контейнере открываем 9000 порт и запускаем сервер php-fpm
EXPOSE 9000
CMD ["php-fpm"]