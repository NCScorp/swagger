FROM nasajon/php:7.1-fpm-symfony
MAINTAINER DevOps <devops@nasajon.com.br>

ENV ENV "production"
USER nginx
COPY . /var/www/html/
USER root

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/logs && chown -R nginx:www-data /var/www/html/var
