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

## Notas

- Mantén tus credenciales fuera del código fuente.
- Si cambias de entorno o PC, recuerda siempre configurar el archivo `.env`.
