# 1. Imagen pura de PHP
FROM php:8.2-cli

# 2. Instalamos dependencias
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

# 3. Instalamos extensión pdo_pgsql
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# 4. Traemos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Nos paramos en la carpeta base del servidor
WORKDIR /var/www/html

# 6. Copiamos TODO tu repositorio de GitHub adentro
COPY . .

<<<<<<< HEAD
=======
# 🔥 EL TRUCO MAESTRO: Entramos a la subcarpeta donde realmente está Laravel
WORKDIR /var/www/html/dkaizen-api

>>>>>>> ed30d1b0a7d5b8b679b8da4ce273552b7f443a9a
# 7. Instalamos las dependencias
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 8. Exponemos el puerto
EXPOSE 8000

# 9. Comando para encender el servidor
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]