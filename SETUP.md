# SETUP — TiendaStock

Todo se ejecuta dentro de `src/`.

---

## 1. Instalación desarrollo (PC nueva)

```bash
cd ruta/a/TiendaStock/src

copy .env.example .env
# EDITAR .env: poner DB_DATABASE, DB_USERNAME, DB_PASSWORD (y APP_LOCALE=es)

composer install
php artisan key:generate
npm install
php artisan migrate
npm run build

# Levantar (dos terminales)
Terminal 1: php artisan serve
Terminal 2: npm run dev

# O todo junto:
composer dev
```

App en **http://127.0.0.1:8000** (con hot reload en el frontend)

---

## 2. Actualizaciones

```bash
cd ruta/a/TiendaStock/src

# Si hay nuevos paquetes o se actualizó composer.lock
composer install

# Si hay nuevas migraciones
php artisan migrate

# Si hay nuevos paquetes frontend o se actualizó package-lock.json
npm install

# Recompilar assets
npm run build
```

---

## 3. Producción

```bash
cd ruta/a/TiendaStock/src

copy .env.example .env
# EDITAR .env: APP_ENV=production, APP_DEBUG=false, dominios, etc.

composer install --no-dev --optimize-autoloader
php artisan key:generate
npm install && npm run build
php artisan migrate --force

# Cachear config para rendimiento
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Servir con un virtual host apuntando a `src/public/` o usar `php artisan serve` (solo dev).
