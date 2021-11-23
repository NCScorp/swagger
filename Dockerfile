# FROM nasajon/php:7.1-fpm-symfony
# MAINTAINER Jefferson Santos <jeffersonsantos@nasajon.com.br>

# ENV ENV "production"
# USER nginx
# COPY . /var/www/html/
# USER root

# RUN mkdir -p /var/www/html/var/cache /var/www/html/var/logs && chown -R nginx:www-data /var/www/html/var

# FROM nginx:alpine

# COPY nginx/default.conf /etc/nginx/conf.d/default.conf
# COPY dist /usr/share/nginx/html

FROM nginx:alpine

COPY nginx/default.conf /etc/nginx/conf.d/default.conf
COPY dist /usr/share/nginx/html

FROM nasajon/yarn

# Adjust Time Zone
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Add files
# COPY . /home/node/app

# Setting environment vars
# ENV NODE_ENV=production

# Setting working directory
WORKDIR /home/node/app

USER root

# Running webpack build
CMD node node_modules/.bin/webpack
