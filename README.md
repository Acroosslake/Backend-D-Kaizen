# D'Kaizen - Backend API (Laravel)
"API REST en Laravel para D'Kaizen, un sistema de gestión y reservas para barberías. Desarrollado con arquitectura limpia (Patrón Repositorio e Inyección de Dependencias) como proyecto final para el SENA."

##  Sobre el Proyecto

Este backend funciona como una API RESTful que alimenta la interfaz de usuario desarrollada en React. El sistema está diseñado originalmente como un MVP (Producto Mínimo Viable) enfocado en la gestión de un único barbero/administrador, optimizando los flujos de reservas y la administración básica del negocio.

## 🛠️ Stack Tecnológico
* **Framework:** Laravel 11 (PHP)
* **Base de Datos:** MySQL
* **Entorno:** Docker / GitHub Codespaces
* **Autenticación:** Laravel Sanctum (Tokens)

## 🏗️ Arquitectura del Código

Para garantizar un código limpio, escalable y fácil de mantener, este proyecto implementa **Inyección de Dependencias** y está estructurado bajo el **Patrón Repositorio**. La lógica se divide en tres capas principales:

1. **Controllers (`app/Http/Controllers/`):** Gestionan las peticiones HTTP de entrada y salida (JSON).
2. **Services (`app/Services/`):** Contienen toda la lógica de negocio y las reglas de la barbería.
3. **Repositories (`app/Repositories/`):** Se encargan exclusivamente de la interacción con la base de datos mediante Eloquent, aislando las consultas del resto de la aplicación.

## 📦 Módulos Principales (MVP)
* **Usuarios y Autenticación:** Gestión de roles (Administrador y Cliente).
* **Gestión de Citas (Reservas):** Agendamiento de servicios sin solapamiento de horarios.
* **Inventario Básico:** Registro de entradas y salidas de productos.
* **Financiero Básico:** Registro plano de ingresos y egresos.

## ⚙️ Configuración y Ejecución (Local / Codespaces)

1. Clonar el repositorio.
2. Copiar el archivo de entorno: `cp .env.example .env`
3. Instalar dependencias de PHP: `composer install`
4. Levantar los contenedores de Docker (Laravel Sail): `./vendor/bin/sail up -d`
5. Generar la clave de la aplicación: `./vendor/bin/sail artisan key:generate`
6. Ejecutar las migraciones de la base de datos: `./vendor/bin/sail artisan migrate`

---
*Desarrollado por Julian David Vergara Ramirez.*
