# SETUP — TiendaStock

Todo se ejecuta dentro de `src/`.

---

## 1. Instalación de desarrollo

```bash
cd ruta/a/TiendaStock/src

copy .env.example .env
# EDITAR .env: poner DB_DATABASE, DB_USERNAME, DB_PASSWORD (y APP_LOCALE=es)

composer install
php artisan key:generate
npm ci
php artisan migrate
npm run build
php artisan storage:link

# Levantar (dos terminales)
Terminal 1: php artisan serve
Terminal 2: npm run dev

# En Windows:
composer dev:win

# En sistemas compatibles con pcntl:
composer dev
```

App en **http://127.0.0.1:8000** (con hot reload en el frontend)

Las cargas nuevas guardan las imágenes en MySQL. `storage:link` sigue siendo
necesario para imágenes históricas que permanezcan en el disco público.

---

## 2. Actualizaciones

```bash
cd ruta/a/TiendaStock/src

# Si hay nuevos paquetes o se actualizó composer.lock
composer install

# Si hay nuevas migraciones
php artisan migrate

# Si hay nuevos paquetes frontend o se actualizó package-lock.json
npm ci

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
npm ci && npm run build
php artisan migrate --force

# Cachear config para rendimiento
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Servir con un virtual host apuntando a `src/public/` o usar `php artisan serve` (solo dev).

---

## 4. Railway

La aplicación Laravel está en `src/`. Crea el servicio web desde el repositorio y
configura **Root Directory** como `/src`. El archivo de configuración Railway
está en la raíz del repositorio y define build, inicio y health check. No subas
`.env` ni credenciales.

Agrega un servicio MySQL de Railway y configura estas variables en el servicio web:

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate with php artisan key:generate --show>
APP_URL=https://<your-public-domain>
APP_BUSINESS_TIMEZONE=America/Argentina/Buenos_Aires
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

Reemplaza `MySQL` por el nombre exacto del servicio MySQL en Railway. Laravel usa
`DB_URL` para la conexión. Las migraciones crean las tablas `sessions` y `cache`.
Los timestamps persistidos permanecen en UTC; `APP_BUSINESS_TIMEZONE` configura el
calendario de las métricas. No cambies `APP_KEY` después del primer despliegue:
invalidaría datos cifrados y sesiones. `SESSION_SECURE_COOKIE=true` limita la
cookie a HTTPS. Laravel confía en el proxy de Railway solo para `X-Forwarded-Proto`
y `X-Forwarded-Host`, que Railway sobrescribe; no se usa `X-Forwarded-For` para
decisiones de acceso. Mantén el servicio accesible a través del proxy de Railway.

El comando de inicio ejecuta `migrate --force`, cachea la configuración y las
rutas, y escucha en el puerto `$PORT` de Railway. Haz y prueba una restauración
de respaldo antes de aplicar cambios de esquema a datos que deban conservarse.
Activa backups de MySQL y valida su restauración. Los assets se construyen con
`npm ci && npm run build`. La cola es síncrona por defecto; si agregas trabajos
en segundo plano, configura un worker separado con
`php artisan queue:work --sleep=3 --tries=3`.

Antes de la primera migración de producción, respalda la base y los archivos de
`storage/app/public/productos`. Después de aplicar la migración de `producto_imagenes`,
ejecuta `php artisan productos:import-images` desde un entorno con acceso tanto
al almacenamiento histórico como a la base MySQL destino. El comando es
idempotente y no elimina archivos fuente. Verifica una restauración antes de
retirar copias históricas.

No ejecutes `db:seed` en producción: el seeder de demostración lo rechaza. Para
crear la primera cuenta ADMIN, ejecuta `php artisan admin:create` en un entorno
conectado de forma segura a la base y almacena las credenciales fuera del código.
Después, ADMIN administra las cuentas desde la aplicación.

Antes del primer lanzamiento, verifica credenciales de Railway, conexión MySQL,
HTTPS y una restauración real. Esas operaciones no se validan desde este entorno.

## 5. Pruebas y build

Desde `src/`:

```bash
composer test
npm test
npm run build
```

La suite PHP usa SQLite en memoria. Para ejecutar también contra una instancia
MySQL de pruebas local en `127.0.0.1:3307`, usa `composer test:mysql`; nunca
apuntes ese perfil a una base con datos reales.
