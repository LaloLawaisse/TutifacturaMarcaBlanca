# Entorno de Desarrollo

Requisitos
- PHP 8.x, Composer 2.x, Node 16+ (si aplica), MySQL/MariaDB.

Pasos
1) `cp .env.example .env` y configurar credenciales locales.
2) `composer install` y `php artisan key:generate`.
3) Migraciones/seeders según módulos habilitados.

