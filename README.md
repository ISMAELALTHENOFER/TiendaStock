# TiendaStock

Sistema creado para el stock de la tienda en Amor Eterno Baby y en general

## Configuración y seguridad

1. **Variables de entorno:**
   - Copia el archivo `.env.example` como `.env` y completa los datos de conexión a la base de datos.
   - Ejemplo:
     ```env
     DB_HOST=localhost
     DB_USER=usuario
     DB_PASS=contraseña
     DB_NAME=nombre_base
     ```
2. **No subas archivos sensibles**: El archivo `.gitignore` ya está configurado para evitar subir `.env` y `src/config.php`.

## Estructura del proyecto

- `src/config.php`: Lee las variables de entorno y define la configuración de la base de datos.
- `src/db.php`: Capa de conexión a la base de datos (importa la configuración y crea la conexión).
- `src/index.php`: Lógica y vista principal, importa la conexión desde `db.php`.

## Primeros pasos

1. Clona el repositorio.
2. Copia `.env.example` a `.env` y edítalo con tus datos.
3. Instala dependencias si las hubiera (por ejemplo, Composer para PHP o npm para Node.js).
4. Levanta el entorno con Docker o tu servidor local.

## Frontend React (coexistencia con Blade)

La aplicación utiliza React para las principales pantallas (dashboard, ventas, productos, categorías, usuarios) con coexistencia incremental de Blade.

### Driver de presentación

`src/config/frontend.php` controla qué motor renderiza cada ruta:

| Variable | Ruta | Default |
|----------|------|---------|
| `FRONTEND_DRIVER` | Global (todas las rutas) | `react` |
| `FRONTEND_DASHBOARD_DRIVER` | `/dashboard` | `react` |
| `FRONTEND_VENTAS_INDEX_DRIVER` | `/ventas` | `react` |
| `FRONTEND_VENTAS_POS_DRIVER` | `/ventas/pos` | `react` |
| `FRONTEND_VENTAS_SHOW_DRIVER` | `/ventas/{id}` | `react` |
| `FRONTEND_PRODUCTOS_INDEX_DRIVER` | `/productos` | `react` |
| `FRONTEND_PRODUCTOS_CREATE_DRIVER` | `/productos/create` | `react` |
| `FRONTEND_PRODUCTOS_EDIT_DRIVER` | `/productos/{id}/edit` | `react` |
| `FRONTEND_CATEGORIAS_INDEX_DRIVER` | `/categorias` | `react` |
| `FRONTEND_ADMIN_USERS_INDEX_DRIVER` | `/admin/users` | `react` |
| `FRONTEND_ADMIN_USERS_CREATE_DRIVER` | `/admin/users/create` | `react` |
| `FRONTEND_ADMIN_USERS_EDIT_DRIVER` | `/admin/users/{id}/edit` | `react` |

### Rollback global

```env
FRONTEND_DRIVER=blade
```

Esto restaura todas las superficies Blade para todas las rutas migradas. Para rollback por ruta, configura la variable específica (ej. `FRONTEND_VENTAS_INDEX_DRIVER=blade`).

### Build

```bash
cd src && npm run build
```

Los componentes React están en `src/resources/js/react/`. El host Blade (`resources/views/react/app.blade.php`) monta el árbol React y provee datos iniciales vía `data-props`.

## Notas

- Mantén tus credenciales fuera del código fuente.
- Si cambias de entorno o PC, recuerda siempre configurar el archivo `.env`.
