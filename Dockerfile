#FROM yiisoftware/yii2-php:7.4-apache
FROM php:7.4-fpm

RUN apt-get update

# install nginx
RUN apt-get install -y nginx

# COPY NGINX config
RUN rm /etc/nginx/sites-available/* \
 && rm /etc/nginx/sites-enabled/*
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/sites-available/default.conf  /etc/nginx/sites-available/default.conf
COPY docker/nginx/conf.d/php-backend-upstream.conf /etc/nginx/conf.d/php-backend-upstream.conf
COPY docker/nginx/fastcgi_params /etc/nginx/fastcgi_params
RUN ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default.conf

RUN apt update -y && apt install -y nano
#export env
COPY ./env /app/.env

RUN sed -i 's/;clear_env = no/clear_env = no/' /etc/php/7.4/fpm/pool.d/www.conf

COPY . /app/web

WORKDIR /app/web

RUN rm vendor -rf

#COPY ./composer.json .
#COPY ./composer.lock .

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN chmod 777 composer-setup.php
RUN php composer-setup.php
RUN php -r "unlink('/app/composer-setup.php');"

RUN mv composer.phar /usr/local/bin/composer

RUN chmod 0777 /usr/local/bin/composer

RUN mkdir -p /var/www/.composer/cache && chmod -R 0777 /var/www/.composer/cache
RUN cd /app/web && composer install --optimize-autoloader --no-interaction --prefer-dist --no-suggest --ignore-platform-reqs

COPY backend/deploy/common/app-image/start_in_docker.sh /app/start_in_docker.sh
RUN chmod +x /app/start_in_docker.sh

RUN mkdir /app/web/var/log -p && chmod 0777 /app/web/var -R
RUN chmod 0777 /app/web/public -R
ENTRYPOINT ["/app/start_in_docker.sh"]

# Expose ports.
EXPOSE 80