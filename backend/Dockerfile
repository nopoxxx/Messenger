FROM php:8.4-fpm

# Устанавливаем нужные зависимости
RUN apt-get update 

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo pdo_mysql sockets

# Копируем проект в контейнер
COPY --chown=www-data:www-data . /var/www/html

CMD ["php-fpm"]
