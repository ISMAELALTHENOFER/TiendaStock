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

## 3. Producción local

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

---

## 4. Railway (root repository)

The Laravel application and its Railway build configuration live under `src/`.
In Railway, create the web service from this repository and set **Root Directory**
to `/src`. The root-level `railway.json` contains the build/start commands and
health check. Do not commit `.env` or credentials.

Add a Railway MySQL service and set these web-service variables:

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate with php artisan key:generate --show>
APP_URL=https://<your-public-domain>
APP_TIMEZONE=America/Argentina/Buenos_Aires
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

Replace `MySQL` in the Railway reference with the exact MySQL service name. `DB_URL`
is used by Laravel's existing MySQL connection config. The migrations already
create `sessions` and `cache`, so the documented database drivers have schema
support. Keep `APP_KEY` stable after first deployment; changing it invalidates
encrypted data and logged-in sessions. `SESSION_SECURE_COOKIE=true` ensures the
session cookie is HTTPS-only. Do not configure Laravel to trust every proxy or
all forwarded headers: this repository does not enable broad proxy trust. Railway
proxies terminate HTTPS; use the HTTPS `APP_URL` and the secure-cookie setting.

The configured start command runs `migrate --force` and Laravel caches, then binds
to Railway's `$PORT`. Review and back up production data before schema changes.
Enable Railway MySQL backups and verify restore procedures before relying on
them; no backup is useful until restoration has been tested. Assets are built
with `npm ci && npm run build` during the service build. Queueing is synchronous
by default, so jobs run in the web request; if background work is introduced,
switch to a real queue and run a separate worker service with
`php artisan queue:work --sleep=3 --tries=3`.

Before the first production launch, verify Railway credentials, MySQL connectivity,
HTTPS behavior, and an actual backup/restore. These cannot be verified locally.
