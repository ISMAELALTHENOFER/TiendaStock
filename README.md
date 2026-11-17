# TiendaStock

Aplicación de inventario y ventas desarrollada con Laravel, React y MySQL/SQLite. El código Laravel vive en `src/`; este repositorio no usa los antiguos archivos `config.php`, `db.php` ni `index.php` descritos en versiones anteriores de este README.

## Requisitos

- PHP 8.2 o posterior y Composer.
- Node.js y npm.
- SQLite para desarrollo local o MySQL para producción.

## Instalación y desarrollo

Sigue [SETUP.md](SETUP.md) para preparar el entorno, configurar la base de datos y ejecutar la aplicación. Los comandos de Laravel, Composer y npm se ejecutan desde `src/`.

Para desarrollo local se puede iniciar Laravel y Vite en terminales separadas. `composer dev` también inicia la cola y Pail en sistemas compatibles; en Windows usa `composer dev:win`.

## Presentación y permisos

React es la interfaz principal de Dashboard, Ventas, Productos, Categorías y Usuarios. Las rutas conservan una alternativa Blade mediante `FRONTEND_DRIVER` y los drivers específicos de `src/config/frontend.php`. Autorización, validación y cambios de datos se aplican en Laravel; ocultar acciones en React no sustituye los controles del servidor.

Autenticación y perfil permanecen en Blade mientras continúa la migración del resto de las pantallas.

## Verificación

Desde `src/`:

```bash
composer test
npm test
npm run build
```

Las pruebas PHP usan SQLite en memoria por defecto. Para verificar también MySQL,
inicia una instancia de prueba local en `127.0.0.1:3307` y ejecuta
`composer test:mysql`. Consulta [SETUP.md](SETUP.md) para el despliegue.
