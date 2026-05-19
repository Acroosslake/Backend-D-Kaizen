# 1. Imagen pura de PHP
FROM php:8.2-cli

# 2. Instalamos las dependencias, agregando libpq-dev (vital para Postgres)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Instalamos la extensión pdo_pgsql para conectar a la base de datos
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# 4. Traemos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Directorio de trabajo
WORKDIR /var/www/html

# 🔥 6. EL TRUCO ESTÁ AQUÍ: Copiamos TODO tu código al contenedor
COPY . .

# 🔥 7. Instalamos las dependencias de Laravel directamente en la imagen
RUN composer install --no-dev --optimize-autoloader

# 8. Exponemos el puerto 8000
EXPOSE 8000

# 9. Comando para encender el servidor
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]