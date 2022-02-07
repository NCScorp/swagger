FROM nasajon/php:7.1-fpm-symfony
MAINTAINER Jefferson Santos <jeffersonsantos@nasajon.com.br>

USER nginx
COPY . /var/www/html/
USER root
RUN mkdir /var/www/html/var/cache /var/www/html/var/logs && chown -R nginx:www-data /var/www/html/var 
