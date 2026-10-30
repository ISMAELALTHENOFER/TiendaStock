# Estado general de TiendaStock

Panorama del sistema según el código del repositorio al 24 de octubre de 2026. Describe funcionalidades implementadas y configuración declarada; no certifica el estado de una instalación en producción.

## Resumen

TiendaStock es una aplicación web de gestión de inventario y ventas. El proyecto ejecutable está en `src/`. Laravel atiende las rutas, aplica permisos, procesa las operaciones y persiste los datos; la interfaz combina vistas Blade con páginas React. Las páginas migradas usan React de forma predeterminada y conservan una alternativa Blade configurable.

## Módulos y funciones

| Módulo | Qué ofrece | Referencia principal |
| --- | --- | --- |
| Acceso y perfil | Inicio de sesión, recuperación de contraseña, verificación de correo y edición de perfil. No se expone una ruta pública de registro. | [`src/routes/auth.php`](src/routes/auth.php), [`src/routes/web.php`](src/routes/web.php) |
| Dashboard | Indicadores de ventas y productos, alertas de bajo stock, analítica por período, actividad reciente y accesos rápidos según rol. | [`src/resources/js/react/dashboard.jsx`](src/resources/js/react/dashboard.jsx), [`src/app/Http/Controllers/DashboardAnalyticsController.php`](src/app/Http/Controllers/DashboardAnalyticsController.php) |
| Categorías | Consulta y administración de categorías del catálogo. | [`src/routes/web.php`](src/routes/web.php), [`src/app/Models/Categoria.php`](src/app/Models/Categoria.php) |
| Productos e inventario | Alta, edición, consulta, imágenes, búsqueda y filtros. La baja desactiva el producto en vez de eliminarlo; los productos sin existencias no se ofrecen como disponibles. | [`src/app/Http/Controllers/ProductoController.php`](src/app/Http/Controllers/ProductoController.php), [`src/resources/js/react/productos.jsx`](src/resources/js/react/productos.jsx) |
| Ventas | Punto de venta, historial, detalle/recibo imprimible y anulación. La venta descuenta existencias en una transacción; la anulación las restituye. | [`src/app/Http/Controllers/VentaController.php`](src/app/Http/Controllers/VentaController.php), [`src/resources/js/react/sales.jsx`](src/resources/js/react/sales.jsx) |
| Usuarios | Listado y administración de cuentas y roles. | [`src/resources/js/react/users.jsx`](src/resources/js/react/users.jsx), [`src/routes/web.php`](src/routes/web.php) |
| Actividad | Registro de eventos de negocio luego de confirmar la transacción y consulta de actividad reciente. | [`src/app/Services/ActivityRecorder.php`](src/app/Services/ActivityRecorder.php), [`src/app/Http/Controllers/DashboardActivityController.php`](src/app/Http/Controllers/DashboardActivityController.php) |

### Roles y permisos

El dashboard requiere sesión iniciada y correo verificado. `ADMIN` administra usuarios y accede a ventas e inventario; `Ventas` accede a ventas; `Control Stock` administra categorías y productos. Las restricciones se aplican en el servidor desde [`src/routes/web.php`](src/routes/web.php), no únicamente en el menú. Los endpoints de búsqueda y datos de productos requieren autenticación, pero no un rol específico: revisar ese alcance antes de exponer información sensible adicional.

## Cómo está construido

1. [`src/routes/web.php`](src/routes/web.php) y [`src/routes/auth.php`](src/routes/auth.php) definen el acceso y derivan solicitudes a controladores Laravel.
2. Los controladores validan y procesan operaciones; los modelos Eloquent representan usuarios, categorías, productos, ventas e ítems de venta. Los servicios de analítica y actividad separan esas tareas del controlador.
3. [`src/config/frontend.php`](src/config/frontend.php) selecciona el frontend: React por defecto en páginas migradas, con cambio global mediante `FRONTEND_DRIVER=blade` o por página mediante variables `FRONTEND_*_DRIVER=blade` cuando el driver global sigue siendo React.
4. [`src/resources/views/layouts/app.blade.php`](src/resources/views/layouts/app.blade.php) aloja la página correspondiente; [`src/resources/views/react/app.blade.php`](src/resources/views/react/app.blade.php) entrega las propiedades y [`src/resources/js/react/main.jsx`](src/resources/js/react/main.jsx) monta la aplicación. Autenticación y perfil permanecen en Blade.

Las imágenes de productos se almacenan en el disco público; para servirlas se requiere el enlace de almacenamiento indicado en [`SETUP.md`](SETUP.md).

## Tecnologías y herramientas

| Capa | Tecnología declarada | Fuente |
| --- | --- | --- |
| Servidor | PHP `^8.2`, Laravel `^12.0`, Eloquent; Laravel Breeze `^2.3` para el andamiaje de autenticación. | [`src/composer.json`](src/composer.json) |
| Interfaz | Blade, React y React DOM `^19.2.4`, JavaScript, Alpine.js `^3.4.2`. | [`src/package.json`](src/package.json), [`src/resources/views/layouts/app.blade.php`](src/resources/views/layouts/app.blade.php) |
| Estilos y compilación | Tailwind CSS `^3.1.0`, CSS propio y Vite `^7.0.0`. | [`src/package.json`](src/package.json), [`src/vite.config.js`](src/vite.config.js) |
| Datos | Base de datos configurable; SQLite es el valor predeterminado de la configuración, que también contempla MySQL, MariaDB y PostgreSQL. El motor real depende del entorno. | [`src/config/database.php`](src/config/database.php) |
| Pruebas | PHPUnit `^11.5.3`; suites Unit y Feature con SQLite en memoria, sesión/caché en memoria y cola síncrona. | [`src/composer.json`](src/composer.json), [`src/phpunit.xml`](src/phpunit.xml) |

Los rangos anteriores son los **declarados en los manifiestos**, no versiones instaladas o verificadas en un servidor. El repositorio mantiene sus conjuntos de dependencias en `src/composer.lock` y `src/package-lock.json`.

## Estilo visual y colores

La interfaz utiliza fondos claros con superficies blancas, texto azul oscuro, barra lateral oscura y verde como acento principal. Los estados usan colores específicos para advertencia y error. Los valores vigentes están definidos como variables CSS en [`src/resources/css/app.css`](src/resources/css/app.css) y se complementan con tokens de [`src/tailwind.config.js`](src/tailwind.config.js).

| Uso | Variable | Color |
| --- | --- | --- |
| Fondo general | `--canvas` | `#f8f9ff` |
| Superficie / superficie tenue | `--surface` / `--surface-muted` | `#ffffff` / `#eff4ff` |
| Texto principal / secundario | `--ink` / `--muted` | `#0b1c30` / `#565e74` |
| Bordes / barra lateral | `--border` / `--sidebar` | `#dce9ff` / `#213145` |
| Acción principal / hover | `--primary` / `--primary-hover` | `#006948` / `#00855d` |
| Foco | `--focus` | `#005137` |
| Advertencia / error | `--warning` / `--danger` | `#ca8a04` / `#dc2626` |

La tipografía general usa **Inter** y los títulos **Plus Jakarta Sans**. Hay componentes de tarjetas, formularios, tablas y estados de carga/error, además de reglas adaptables a pantallas pequeñas y preferencia de movimiento reducido en [`src/resources/css/app.css`](src/resources/css/app.css).

## Estado de verificación y límites

Este documento refleja una **inspección estática del repositorio**. No se ejecutaron la aplicación, las migraciones, las pruebas ni la compilación de producción para este informe; tampoco se verificaron disponibilidad, datos reales, seguridad operativa o despliegue. Para comprobar el estado funcional local, el proyecto documenta `composer test` y `npm run build` desde `src/`; no hay un script de test o lint JavaScript declarado en [`src/package.json`](src/package.json). La preparación del entorno está en [`SETUP.md`](SETUP.md).
