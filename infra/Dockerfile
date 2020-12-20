FROM debian:buster

RUN apt update \
    && apt install -y \
        supervisor \
        nginx \
        wget \
        git \
        unzip \
        php \
        php-fpm \
        php-zip \
        php-ctype \
        php-iconv \
        php-dom \
        php-xml \
        php-json \
        php-pdo

COPY ./infra/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./infra/nginx/game-of-life.net /etc/nginx/sites-available/game-of-life.net

RUN ln -s /etc/nginx/sites-available/game-of-life.net /etc/nginx/sites-enabled/ \
    && rm /etc/nginx/sites-enabled/default \
    && rm /etc/nginx/sites-available/default

WORKDIR /var/www/game-of-life.net

COPY . /var/www/game-of-life.net

RUN mkdir -p ./var/cache ./var/log \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && [ "$(php -r "echo hash_file('sha384', 'composer-setup.php');")" = "$(wget -q -O - https://composer.github.io/installer.sig)" ] \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && /usr/local/bin/composer install \
    && chown -R www-data:www-data /var/www/game-of-life.net

CMD ["/usr/bin/supervisord"]
