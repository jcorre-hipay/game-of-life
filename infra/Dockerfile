FROM debian:buster

WORKDIR /var/www/game-of-life.net

RUN apt update \
    && apt install -y \
        supervisor \
        nginx \
        sqlite \
        wget \
        git \
        unzip \
        php \
        php-fpm \
        php-intl \
        php-zip \
        php-ctype \
        php-iconv \
        php-dom \
        php-xml \
        php-json \
        php-pdo \
        php-sqlite3

COPY ./infra/supervisor/supervisord.conf /etc/supervisord.conf
COPY ./infra/nginx/game-of-life.net /etc/nginx/sites-available/game-of-life.net
COPY ./infra/bootstrap.sh /root/bootstrap.sh

COPY . /var/www/game-of-life.net

RUN ln -s /etc/nginx/sites-available/game-of-life.net /etc/nginx/sites-enabled/ \
    && rm /etc/nginx/sites-enabled/default \
    && rm /etc/nginx/sites-available/default \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && [ "$(php -r "echo hash_file('sha384', 'composer-setup.php');")" = "$(wget -q -O - https://composer.github.io/installer.sig)" ] \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

ENTRYPOINT ["bash", "/root/bootstrap.sh"]
