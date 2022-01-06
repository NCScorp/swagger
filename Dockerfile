FROM nasajon/php:7.1-fpm-symfony

USER nginx
COPY . /var/www/html/
USER root
COPY default.conf /etc/nginx/conf.d

# Comando adicionado para corrigir uma url errada, liberada no desktop
# RUN sed -i -e '/root \/var\/www\/html/a   rewrite \^\/nasajon\/usuario$ https:\/\/atendimento.nasajon.com.br\/nasajon permanent;' /etc/nginx/conf.d/default.conf

RUN cp app/config/parameters.docker.dist app/config/parameters.yml && \ 
    mkdir -p app/cache/prod/htmlpurifier && \
    chown -R nginx:www-data /var/www/html/app/cache /var/www/html/app/logs
