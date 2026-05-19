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

# 🔥 6. EL TRUCO ESTÁ AQUÍ: Copiamos específicamente el contenido de la carpeta de Laravel
COPY dkaizen-api/ .

# 🔥 7. Ahora sí, composer encontrará el archivo y descargará las dependencias
RUN composer install --no-dev --optimize-autoloader

# 8. Exponemos el puerto
EXPOSE 8000

# 9. Comando para encender el servidor adaptado a Render
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}