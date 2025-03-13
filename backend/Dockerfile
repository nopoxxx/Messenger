FROM php:8.4-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    supervisor \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы composer
COPY composer.json composer.lock ./

# Устанавливаем зависимости через Composer
RUN composer install --no-dev --optimize-autoloader

# Копируем весь проект в контейнер
COPY . .

# Устанавливаем права для веб-сервера
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Копируем конфигурацию Supervisor
COPY ws/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Открываем порт для WebSocket (если используется)
EXPOSE 8080

# Запускаем Supervisor
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
