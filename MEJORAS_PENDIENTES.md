# TiendaStock — Mejoras y correcciones pendientes

Hoja de ruta basada en la auditoría del 26/10/2026 y las decisiones posteriores. Las rutas son relativas a `src/` salvo indicación. Verificar el estado actual antes de implementar: las referencias y hallazgos históricos pueden haber cambiado.

Complementa a `tiendastock-mejoras-ui-responsive.md`. Ante diferencias, prevalecen las decisiones de este documento. Incluye **bugs, seguridad, eficiencia, interfaz, despliegue y deuda técnica**.

Severidad: 🔴 alta (corregir primero) · 🟠 media · 🟢 baja.
Verificado manualmente: marcado con ✔. El resto surge de lectura de código, sin reproducir.

---

## 1. Ventas — integridad de datos y dinero 🔴

> Cambio de alto riesgo: verificar integridad, permisos y concurrencia con pruebas. No crear commits ni publicar sin autorización explícita.

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 1.1 ✔ | El servidor guarda `subtotal`, `descuento`, `total`, `pago_con` tal como los manda el cliente. Un usuario Ventas puede postear `total=1` por productos caros y el dashboard queda falseado. | `app/Http/Controllers/VentaController.php:78-83`, `app/Http/Requests/StoreVentaRequest.php:23-27` | Calcular y validar importes en el servidor usando los precios verificados que se mostraron al agregar los productos al carrito, con descuento máximo del 50 %. Derivar total y cambio y probar la manipulación de importes. Ver «Importes de ventas». |
| 1.2 ✔ | El camino de error de stock redirige a `ventas.create`, que **no existe** (la ruta es `ventas.pos`) → error 500. | `VentaController.php:119`, `routes/web.php:61` | Cambiar a `ventas.pos`. Test que fuerce el `RuntimeException`. |
| 1.3 | Un mismo `producto_id` repetido pasa la validación inicial por línea. El control transaccional vuelve a comprobar stock, pero el manejo de error debe verificarse. | `StoreVentaRequest.php:66-77`, `VentaController.php:89-119` | Validar cantidades acumuladas por producto y preservar los precios acordados; rechazar sin cambios si el stock no alcanza. Test. |
| 1.4 | Anular una venta dos veces en paralelo devuelve el stock dos veces (el chequeo `isAnulada()` está fuera del lock). | `VentaController.php:148-161` | Releer con `lockForUpdate()` dentro de la transacción, o `update ... where estado='completada'` y chequear filas afectadas. |
| 1.5 | Se pueden intentar vender productos desactivados con un POST armado. | `StoreVentaRequest.php:19` | Verificar estado y stock también dentro de la transacción con bloqueo; rechazar productos sin stock y probar carreras. |
| 1.6 | Dinero manejado como `float`; `Producto` sin casts `decimal:2`. | `VentaController.php:83,105`, `StoreVentaRequest.php:58`, `app/Models/Producto.php:30-35,69` | Casts `decimal:2` y aritmética en centavos (enteros) o `bcmath`, redondeando una sola vez. |
| 1.7 | `Producto::find()` por cada ítem dentro del validador (N+1). | `StoreVentaRequest.php:66-77` | Un solo `whereIn('id', ...)->get()->keyBy('id')`. |

## 2. Datos y seguridad 🔴/🟠

> Las migraciones de 2.1 cambian el esquema persistido: requieren respaldo y pruebas de restauración antes de aplicarlas a datos que deban conservarse.

| # | Sev | Problema | Dónde | Corrección |
|---|---|---|---|---|
| 2.1 ✔ | 🔴 | Borrado en cascada: eliminar un usuario borra **todas sus ventas**; eliminar un producto borra sus ítems de venta. Cualquier usuario puede borrar su propia cuenta desde Perfil. **Decidido (26/10/2026): nadie puede borrar su cuenta; las ventas no se pueden perder.** | `database/migrations/2026_07_28_000010_create_ventas_table.php:13`, `..._000011_create_venta_items_table.php:14`, `ProfileController.php:34-49` | Ver 2.1.a y 2.1.b. |

**2.1.a — Eliminar el borrado de cuenta (todos los roles, incluido ADMIN):**
- `routes/web.php:28`: quitar la ruta `DELETE /profile` (`profile.destroy`).
- `app/Http/Controllers/ProfileController.php:34-49`: quitar el método `destroy`.
- `resources/views/profile/partials/delete-user-form.blade.php`: borrar el archivo; quitar el `@include` en `resources/views/profile/edit.blade.php:28`.
- Tests en `tests/Feature/ProfileTest.php`: reemplazar `test_user_can_delete_their_account` (l.130) y `test_correct_password_must_be_provided_to_delete_account` (l.148) por un test que verifique que `DELETE /profile` responde 405/404 y el usuario y sus ventas siguen existiendo; ajustar las aserciones de `profile.destroy` en l.81-108 (`test_profile_danger_treatment_uses_the_existing_panel`, `test_profile_forms_fill_...`).
- La administración de usuarios no tiene `destroy` (`routes/web.php:56`). Implementar desactivación por ADMIN según la sección 12, nunca borrado.

**2.1.b — Protección a nivel base de datos (por si algo borra igual):**
- Nueva migración: `ventas.user_id` y `venta_items.producto_id` con `restrictOnDelete()` (reemplaza el `cascade`). `venta_items.venta_id` puede mantener cascade (las ventas no se borran, se anulan).
- Test: intentar borrar un usuario o producto con ventas falla y las ventas persisten.
- Es cambio de esquema: respaldar la base antes de aplicarlo a datos que deban conservarse.

| # | Sev | Problema | Dónde | Corrección |
|---|---|---|---|---|
| 2.2 | 🟠 | El endpoint de actividad serializa el `User` completo (email, rol) y el modelo afectado (incluye `precio_compra`) a cualquier usuario verificado. | `app/Http/Controllers/DashboardActivityController.php:18-19` | Retirar la tarjeta de actividad del dashboard; revisar usos antes de retirar el endpoint y no mantener datos sensibles expuestos sin necesidad. Ver «Dashboard» en decisiones tomadas. |
| 2.3 | 🟠 | Un ADMIN puede degradarse a sí mismo o al último ADMIN → nadie administra más. | `app/Http/Requests/Admin/UpdateUserRequest.php:36`, `Admin/UserController.php:72-91` | Prohibir cambiar el rol del último ADMIN o el propio. Test. |
| 2.4 | 🟢 | `$request->all()` en create/update de categorías. | `CategoriaController.php:71,129` | Usar `$request->validated()`. |
| 2.5 | ~~🟢~~ | **Reemplazado por sección 12.** Usuarios creados por admin quedan sin `email_verified_at` y el middleware `verified` los bloquea si no hay mail. | `app/Models/User.php:11`, `Admin/UserController.php:50-56` | Setear `email_verified_at` al crearlos desde admin. |
| 2.6 | 🟢 | El seeder crea el admin con contraseña por defecto. | `database/seeders/DatabaseSeeder.php:18-22` | Leerla de `.env` o impedir correr el seeder en `production`. |

Preservar `.env` fuera del control de versiones, CSRF y validación de imágenes. Actualizar la autorización por rol en el servidor conforme a las decisiones de visibilidad y propiedad.

## 3. Eficiencia (backend) 🟠

> **Skill:** `ponytail:ponytail-review` para evitar sobre-ingeniería al optimizar.

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 3.1 | Faltan índices: `ventas(estado, created_at)`, `productos(activo, cantidad)`, `productos(nombre)`; `categorias.nombre` sin `unique` (solo validación, carrera posible). | migraciones | Una migración con esos índices y el unique. |
| 3.2 | `/productos/data` devuelve **todo** el catálogo sin paginar; el front filtra y pagina en el navegador. | `ProductoController.php:42-61`, `resources/js/react/productos.jsx:350-392` | Paginar en servidor y usar el hook `lib/useLiveSearchResults.js` como ya hacen Usuarios y Categorías. |
| 3.3 | "Valor total" del dashboard suma `precio_venta` sin multiplicar por stock e incluye inactivos. Además es una closure en rutas. | `routes/web.php:21` | `SUM(precio_venta * cantidad)` con `activo = true`, mover a controlador. |
| 3.4 | Listado de ventas carga todos los ítems de cada venta. | `VentaController.php:20` | `withCount('items')` en lugar de `with('items')`. |

## 4. Multiplataforma y portabilidad 🟠

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 4.1 | Zona horaria `UTC`: en Argentina las ventas después de las 21:00 cuentan para el día siguiente en "Ventas hoy" y gráficos. | `config/app.php:68`, `app/Services/DashboardAnalytics.php:15-23` | `APP_TIMEZONE=America/Argentina/Buenos_Aires` (configurable por `.env`) y agrupar por día en esa zona. Test. |
| 4.2 | Migración de backfill usa `INSTR` (no existe en PostgreSQL) y puede generar usernames duplicados. | `database/migrations/2026_07_28_000002_backfill_usernames.php:12` | Backfill en PHP fila por fila, sufijo para duplicados. |
| 4.3 | Búsqueda de duplicados con `strtolower` + `LOWER()` falla con acentos/ñ; `LIKE` es sensible a mayúsculas en PostgreSQL. | `ProductoController.php:229,254-258`, `CategoriaController.php:47`, `VentaController.php:26-28` | `mb_strtolower` y normalizar el nombre guardado, o documentar la collation requerida. |
| 4.4 | `composer dev` usa `pail`, que requiere `pcntl` (no existe en Windows). | `composer.json:49` | Script `dev:win` sin pail, o documentarlo. |
| 4.5 | Tests solo en SQLite `:memory:`: no prueban los bloqueos y diferencias de MySQL. | `phpunit.xml:26-27` | Ejecutar pruebas específicas con MySQL para concurrencia y claves foráneas; definir el entorno de prueba sin usar datos reales. |
| 4.6 | Tests JS (`node:test`) sin script npm, repartidos en dos carpetas. | `package.json`, `tests/js/`, `resources/js/react/lib/*.test.js` | `"test": "node --test"` (funciona en todos los SO) y unificar carpeta. |
| 4.7 | Vite escucha en todas las interfaces por defecto (expuesto en la LAN). | `vite.config.js:11` | Default `localhost`; LAN solo con `VITE_DEV_HOST`. |
| 4.8 | Fuentes desde CDN (fonts.bunny.net): el POS sin internet pierde tipografía. Tres familias distintas. | `resources/views/layouts/app.blade.php:14`, `landing.blade.php:10` | Una sola familia empaquetada con Vite. |

Ya está bien: finales de línea LF forzados por `.gitattributes`, scripts npm neutrales, sin `public/hot` colgado.

## 5. Frontend — bugs y accesibilidad 🔴/🟠

> **Skills:** `accessibility` (auditoría WCAG 2.2), `impeccable` (modo *harden* para estados y bordes).

| # | Sev | Problema | Dónde | Corrección |
|---|---|---|---|---|
| 5.1 ✔ | 🔴 | El focus-trap del Modal incluye `<input type="hidden">` → Tab queda trabado en el formulario de producto. | `resources/js/react/components/ui/Modal.jsx:21` | Selector: `button:not([disabled]), input:not([type=hidden]):not([disabled]), select, textarea, a[href], [tabindex]:not([tabindex="-1"])`. |
| 5.2 | 🔴 | Con dos modales abiertos (Nueva Categoría dentro de Producto), Escape cierra el exterior y se pierde lo cargado. | `Modal.jsx:12-27`, `productos.jsx:270` | Pila de modales: solo el de arriba maneja teclado. |
| 5.3 | 🔴 | El Toast de flash no se puede cerrar y en móvil tapa el botón de menú. | `resources/js/react/app.jsx:26`, `components/ui/Toast.jsx:24` | Estado `dismissed`, `onClose` y auto-ocultar. |
| 5.4 | 🟠 | Menú de acciones de venta (`<details>`) se corta dentro del `overflow-x-auto` y no cierra con Escape/click afuera. | `sales.jsx:53-55,102` | Usar el `Dropdown` compartido en un portal. |
| 5.5 | 🟠 | Nueva categoría se agrega con `document.createElement` a un `<select>` de React: se pierde al re-render y no llega al filtro. | `productos.jsx:137-143` | Categorías en estado React + callback `onCategoryCreated`. |
| 5.6 | 🟠 | Dropdown de cuenta declara `role="menu"` sin navegación por flechas ni foco. | `components/ui/Dropdown.jsx:15` | Tratarlo como *disclosure* (sin roles menu) y cerrar en `focusout`. |
| 5.7 | 🟠 | Páginas sin `<h1>` (Productos, Usuarios, Categorías) y textos en inglés ("Close", "Loading…", "Workspace"). | `productos.jsx:416`, `users.jsx:34`, `categorias.jsx:326`, `Modal.jsx:37`, `Toast.jsx:24`, `LoadingState.jsx:1`, `Sidebar.jsx:5` | `<h1>` por página, textos en español. |
| 5.8 | 🟠 | Gráfico de ventas: valores solo en hover (`<title>`), etiquetas de 10 px; inusable en táctil. | `dashboard.jsx:31` | Mostrar valores o tabla oculta accesible; etiquetas ≥ 12 px. |
| 5.9 | 🟢 | "Activar" en `text-green-600` sobre blanco: contraste ~3.3:1 (no cumple AA). | `productos.jsx:320` | `text-green-700` o superior. |
| 5.10 | 🟢 | `id="modal-title"` fijo; `window.alert` para validar fechas; `URL.createObjectURL` sin liberar. | `Modal.jsx:37`, `sales.jsx:81`, `productos.jsx:71` | `useId`, error inline, `revokeObjectURL` en cleanup. |

## 6. Vistas modernas y responsive 🟠

> **Skills:** `impeccable` (rediseño/pulido), `frontend-design` (dirección visual), `responsive-design` o `fronted-responsive` (mobile-first), `accessibility`. El detalle por pantalla ya está en `tiendastock-mejoras-ui-responsive.md`: seguir ese documento.

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 6.1 ✔ | `tailwind.config.js` pisa la paleta `sky` con grises: todo `sky-*` (Badge "info", Toast info) se ve gris. `blush` y `brand` son copias. | `tailwind.config.js:37-70` | Borrar overrides `sky`, `blush` y `brand` duplicado. |
| 6.2 | Colores definidos dos veces (hex en Tailwind + variables CSS) y sin modo oscuro. | `tailwind.config.js:17-36`, `resources/css/app.css:6-22` | Colores de Tailwind como `rgb(var(--x) / <alpha-value>)`; luego bloque `.dark` en `:root`. |
| 6.3 | iOS Safari hace zoom en inputs de 14 px. | `sales.jsx:184,195`, `categorias.jsx:268,347`, `productos.jsx:441-449`, `dashboard.jsx:73`, `app.css:197` | `text-base sm:text-sm` en controles de formulario. |
| 6.4 | Sin PWA: falta `manifest.webmanifest`, `theme-color`, `apple-touch-icon`. `<main>` de React sin `safe-area-inset`. | `public/`, `layout/AppShell.jsx:36` | Manifest + íconos (instalable en celular/tablet del local); copiar padding safe-area del layout Blade. |
| 6.5 | Sidebar olvida si estaba colapsada (cada navegación recarga) y no marca activa `categorias.edit`. | `layout/AppShell.jsx:7`, `Sidebar.jsx:6` | Guardar en `localStorage` (con try/catch); agregar la ruta. |
| 6.6 ✔ | Umbral de "bajo stock" inconsistente: ≤5 en productos y dashboard, ≤6 en POS. **Decidido (26/10/2026): el umbral es 1** → `0` = Agotado, `1` = Bajo, `≥2` = normal. | `app/Services/DashboardAnalytics.php:11` (`LOW_STOCK_THRESHOLD = 5`), `resources/js/react/productos.jsx:503,524,551,602` (`<= 5`), `resources/js/react/sales.jsx:208` (`available <= 6`) | Backend: `LOW_STOCK_THRESHOLD = 1` y exponerlo en las props (`data-props`) para que el front no lo repita. Front: una sola función `stockTone(cantidad, threshold)` en `lib/` usada por productos, POS y dashboard. Actualizar tests: `tests/Feature/ProductoReactViewContractTest.php:95-99` (asserta el texto `'cantidad <= 5'`) y `tests/Unit/DashboardAnalyticsTest.php:60-69` (fixtures de bajo stock). Agregar test de los tres casos (0, 1, 2). No tocar `sales.jsx:194` ni `pos.blade.php:132`: ese `<= 1` es el mínimo de unidades del carrito, no stock. |

## 7. Eficiencia (frontend) 🟠

> **Skill:** `ponytail:ponytail-review`.

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 7.1 | Alpine, axios y Flatpickr pueden cargarse innecesariamente en páginas React. | `resources/views/layouts/app.blade.php:17,20,108-109` | Revisar usos durante la migración completa a React; retirar dependencias y cargas solo cuando ya no tengan consumidores. |
| 7.2 | Búsqueda del POS dispara un request por tecla, sin cancelar. | `sales.jsx:142-156` | Reusar el patrón debounce 250 ms + `AbortController` de `lib/useLiveSearchResults.js`. |
| 7.3 | Dashboard: cambiar rápido de período puede mostrar datos anteriores; la sección de bajo stock está oculta. | `dashboard.jsx:12,46-52,58,66,71` | Evitar respuestas obsoletas y conservar los indicadores de productos acordados; retirar solo código que resulte innecesario. |
| 7.4 | Imágenes de productos sin `loading="lazy"`, `width`/`height`. | `productos.jsx:489,517,597` | Agregarlos. |
| 7.5 | Un solo bundle de 327 KB sin code-splitting. | `resources/js/react/main.jsx` | `React.lazy` por página en `app.jsx` (cada página es una isla). |

## 8. Deuda técnica y limpieza 🟢

> **Skills:** `ponytail:ponytail-audit` (qué borrar), `documentation-editor` o `cognitive-doc-design` (README).

| # | Problema | Dónde | Corrección |
|---|---|---|---|
| 8.1 | Código duplicado: formato de moneda ×3, `csrf` redefinido, submit con FormData ×3 (usuarios no usa `saveResponse`), badge de stock ×3. | `productos.jsx:18,155`, `sales.jsx:14-21`, `dashboard.jsx:10`, `users.jsx:120`, `categorias.jsx:238` | `lib/format.js` (ya existe `formatMoney`) y `submitForm` en `lib/api.js`. |
| 8.2 | URLs hardcodeadas (`/productos/data`, `/ventas`) en vez del objeto `routes`. Rompe si se despliega en subcarpeta. | `productos.jsx`, `sales.jsx`, `Header.jsx:12-13`, `Sidebar.jsx:15` | Pasarlas por `routes` desde `layouts/app.blade.php:55-65`. |
| 8.3 | Fallbacks Blade duplican React (`productos/index.blade.php` 617 líneas, `ventas/pos.blade.php` 445) y `layouts/navigation.blade.php` no se usa. **Decidido (26/10/2026): React queda definitivo.** | `resources/views/**`, `config/frontend.php`, `resources/views/layouts/app.blade.php` | Ver sección 10. |
| 8.4 | `User::isAtLeast()` sin llamadores. | `app/Models/User.php:84-96` | Confirmar con grep y borrar. |
| 8.5 | README raíz obsoleto (`config.php`, `db.php`, `DB_USER`); `.gitignore` raíz lista `src/config.php`; `composer.json` con nombre `laravel/laravel`. | `../README.md`, `../.gitignore`, `composer.json:3-5` | Reescribir README con el setup real (ver `AGENTS.md`/`SETUP.md`), renombrar paquete. |
| 8.6 | Registro de idioma mezclado: "Gestiona/Ingresa" (tú) vs "Seleccioná/Intentá" (vos). **Decidido (26/10/2026): tuteo neutro profesional.** | Voseo hoy en `resources/js/react/categorias.jsx`, `productos.jsx`, `components/ui/ErrorState.jsx`, `lib/saveResponse.js` | Pasar todo a tú neutro ("Selecciona", "Intenta", "Ingresa", "Puedes"), sin regionalismos ni coloquialismos. Revisar también Blade de auth/perfil, `lang/es/*` y mensajes de validación en `app/Http/Requests/*` (por ejemplo "Debe agregar…" usa usted: unificar a tú). Si algún test asserta textos con voseo, actualizarlo. Hacerlo junto con 5.7 (textos en inglés). |

## 10. Migrar todo el frontend a React 🟠

> Migrar por flujos verificables y preservar la seguridad y el comportamiento del servidor. Un host Blade mínimo para montar React no es una pantalla Blade funcional.

**Alcance:** todo el frontend, incluidas autenticación, perfil y landing, notificaciones y cuadros emergentes, debe estar en React.

1. Planificar la retirada de `FRONTEND_DRIVER` y `FRONTEND_*_DRIVER` de `config/frontend.php` y `.env.example` una vez migrados y verificados los flujos correspondientes.
2. Migrar autenticación, perfil, landing y las pantallas restantes a React, manteniendo sus comportamientos autorizados; retirar los flujos de correo según la sección 12.
3. Adaptar el host, controladores y rutas para montar React y conservar los controles de acceso y validación en el servidor.
4. Retirar las vistas, componentes, drivers y dependencias Blade/Alpine que queden sin uso, después de verificar sus referencias y reemplazos. Conservar solo el host mínimo necesario.
5. Actualizar los tests de presentación y conservar las pruebas de comportamiento HTTP, autenticación, permisos, paginación y ventas; reemplazar las pruebas de fallback por contratos React equivalentes.
6. Docs: actualizar `AGENTS.md`, `src/README.md`, `SETUP.md` y `ESTADO_GENERAL.md` según el resultado.
7. Verificar `composer test`, `npm run build` y los flujos en navegador por rol, además de login y perfil.

## 11. Despliegue en Railway con MySQL 🔴

> **Decidido (26/10/2026): producción en Railway con MySQL.** Hoy el repo no tiene ninguna configuración de despliegue (sin `Procfile`, `nixpacks.toml`, `railway.json` ni `Dockerfile`).
> **Skills:** `security-review` antes del primer deploy; `documentation-editor` para dejar el procedimiento en `SETUP.md`.

| # | Sev | Problema | Dónde | Corrección |
|---|---|---|---|---|
| 11.1 | 🔴 | Las imágenes de productos se guardan en el disco local `public` (`storage/app/public`). En Railway el sistema de archivos del contenedor **se borra en cada deploy** → se pierden todas las imágenes. **Decidido (27/10/2026): guardar en MySQL; Cloudflare R2 queda como alternativa.** | `app/Http/Controllers/ProductoController.php:75,100`, vistas que usan `asset('storage/...')`/`imagen_url` | Ver 11.1.a (plan principal) y 11.1.b (alternativa). |

**11.1.a — Imágenes en MySQL (plan principal):**
- Tabla aparte `producto_imagenes` (`id`, `producto_id` FK `restrictOnDelete`, `mime`, `bytes` `MEDIUMBLOB`, `width`, `height`, `sha1`, timestamps). **No** poner el blob en `productos`: cada listado/búsqueda arrastraría las imágenes.
- Propuesta: redimensionar y convertir a WebP con GD, verificando primero que la extensión y el soporte WebP estén disponibles. Mantener la validación actual (`jpg/png/webp`, 2 MB); tamaño y dimensiones finales requieren verificación.
- Ruta `GET /productos/{producto}/imagen` (con `auth`) que devuelve los bytes con `Content-Type`, `Cache-Control: private, max-age=31536000, immutable` y `ETag` = `sha1`; la URL lleva `?v={sha1}` para invalidar al cambiar la imagen. Así el navegador las pide una sola vez.
- Las consultas de productos nunca seleccionan `bytes`; el front solo recibe la URL.
- Migración de datos: comando artisan que lee los archivos actuales de `storage/app/public/productos`, los inserta en la tabla y deja de usar `storage:link` para productos. Correrlo **antes** del primer deploy (o en local y luego dump).
- Tests: subida guarda fila y no archivo; la ruta devuelve 200 con el `Content-Type` correcto y 304 con `If-None-Match`; listados no incluyen bytes; usuario no autenticado recibe redirect.
- Costos a tener en cuenta: la base crece (≈100 KB × productos), los backups pesan más y cada imagen pasa por PHP. Razonable para un catálogo de cientos/pocos miles de productos.

**11.1.b — Alternativa: Cloudflare R2** (si el catálogo crece mucho o la base se vuelve pesada):
- `composer require league/flysystem-aws-s3-v3`, disco `s3` con endpoint de R2, `FILESYSTEM_DISK=s3`; el código actual (`store('productos', 'public')`) solo cambia el nombre del disco.
- Las imágenes se sirven desde R2/CDN sin pasar por PHP; plan gratis de 10 GB.
- Evaluar una migración a almacenamiento externo solo si se necesita; no se autoriza ningún servicio ni transferencia remota por esta alternativa.

| # | Sev | Problema | Dónde | Corrección |
|---|---|---|---|---|
| 11.2 | 🔴 | La terminación HTTPS en un proxy requiere configurar correctamente proxies y cookies. | `bootstrap/app.php` | Verificar la configuración de Railway y confiar solo en los proxies apropiados; configurar HTTPS y cookies seguras sin permitir cabeceras falsificadas. |
| 11.3 | 🔴 | Falta preparar build y arranque para Railway. | raíz del repo | Definir y verificar configuración con root `src/`, migraciones y arranque; no requerir `storage:link` para imágenes guardadas en MySQL. |
| 11.4 | 🟠 | Conexión a MySQL de Railway. | `config/database.php:20,37` | `DB_CONNECTION=mysql` y `DB_URL=${{MySQL.MYSQL_URL}}` (variable de referencia de Railway). `config/database.php` ya soporta `DB_URL`. |
| 11.5 | 🟠 | Variables de producción. | `.env` de Railway | `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` generada, `APP_TIMEZONE=America/Argentina/Buenos_Aires` (ver 4.1), `LOG_CHANNEL=stderr` (Railway lee la salida estándar), `SESSION_DRIVER=database`, `CACHE_STORE=database`. |
| 11.6 | 🟠 | `QUEUE_CONNECTION` es `database` por defecto: si algo se encola y no hay worker, nunca se ejecuta. | `config/queue.php:16` | Verificar si algo usa colas; si no, `QUEUE_CONNECTION=sync`. Si sí, un segundo servicio Railway con `php artisan queue:work`. |
| 11.7 | 🟠 | `MAIL_MAILER=log`: recuperar contraseña y verificar email **no envían correos** en producción. **Decidido (27/10/2026): sin correo; el ADMIN administra usuarios.** | `config/mail.php:17` | No configurar SMTP. Ver sección 12. |
| 11.8 | 🟠 | El seeder crea el admin con contraseña por defecto (ver 2.6). | `database/seeders/DatabaseSeeder.php:18-22` | Crear el primer admin con un comando artisan que pida contraseña, o leerla de una variable de Railway; nunca `db:seed` con defaults en producción. |
| 11.9 | 🟠 | Backups de la base. | Railway MySQL | Activar backups del servicio MySQL de Railway o un cron `mysqldump`; probar una restauración. Requisito antes de 2.1.b (migración de FKs). |

**Impacto de MySQL en la sección 4:**
- 4.2 (`INSTR`) baja a 🟢: funciona en MySQL. Sigue pendiente el riesgo de usernames duplicados.
- 4.3 baja a 🟢: con collation `utf8mb4_unicode_ci` (default de Laravel) `LIKE` ya ignora mayúsculas y acentos. Usar `mb_strtolower` en PHP igual.
- 4.5 sube a 🔴: los tests corren en SQLite y producción es MySQL; `lockForUpdate` (1.4) y las FKs (2.1.b) solo se prueban de verdad en MySQL. Agregar job de CI o un `phpunit.mysql.xml` para correr local contra MySQL (Docker o Railway de pruebas).

## 12. Gestión de usuarios solo por ADMIN (sin correo) 🔴

> **Decidido (27/10/2026):** no hay recuperación de contraseña ni verificación por correo. El ADMIN crea, edita, resetea contraseñas y desactiva usuarios. Reemplaza al punto 2.5.
> Cambio de autenticación y permisos: verificar controles en el servidor, sesiones y pruebas de acceso por rol.

**12.a — Quitar recuperación y verificación por email:**
- `routes/auth.php:19-30`: quitar `forgot-password` y `reset-password` (GET/POST).
- `routes/auth.php:33-42`: quitar `verify-email`, `verify-email/{id}/{hash}` y `email/verification-notification`.
- Borrar controladores `Auth/PasswordResetLinkController`, `Auth/NewPasswordController`, `Auth/EmailVerificationPromptController`, `Auth/VerifyEmailController`, `Auth/EmailVerificationNotificationController`.
- Borrar vistas `resources/views/auth/forgot-password.blade.php`, `reset-password.blade.php`, `verify-email.blade.php`; quitar el link "¿Olvidaste tu contraseña?" de `login.blade.php`.
- `app/Models/User.php:6,11`: quitar `implements MustVerifyEmail`.
- `routes/web.php:23,32`: quitar el middleware `verified` (dejar `auth`).
- Perfil: el aviso de "email no verificado" en `resources/views/profile/partials/update-profile-information-form.blade.php` (si existe) y el test `test_email_verification_status_is_unchanged_...` (`tests/Feature/ProfileTest.php:111`) dejan de aplicar.
- Tests: borrar `tests/Feature/Auth/PasswordResetTest.php` y `EmailVerificationTest.php`; agregar uno que verifique que `/forgot-password` da 404.
- Se mantienen: login, logout, cambio de contraseña propia desde perfil (`password.update`) y confirmación de contraseña.
- `MAIL_MAILER=log` puede quedar así.

**12.b — Desactivar usuarios (hoy NO existe):**
- El modelo `User` no tiene `activo` y el ADMIN solo puede crear y editar (`routes/web.php:56`, `->except(['show','destroy'])`).
- Migración: `users.activo` boolean default `true`.
- `app/Http/Requests/Auth/LoginRequest.php:45`: permitir el ingreso solo a usuarios activos. Mostrar el aviso de cuenta desactivada únicamente después de comprobar la contraseña; usar un error genérico para credenciales incorrectas o cuentas inexistentes (ver «Conservación y desactivación de cuentas»).
- Usuario desactivado con sesión abierta: middleware (o chequeo en `CheckRole`) que cierra la sesión en el siguiente request.
- `Admin/UserController`: acciones `desactivar` / `activar` (POST, solo ADMIN), con `ConfirmDialog` en `resources/js/react/users.jsx`, badge de estado y filtro "Ver inactivos" (mismo patrón que productos).
- Reglas: el ADMIN no puede desactivarse a sí mismo ni al último ADMIN activo (junto con 2.3).
- Las ventas del usuario desactivado se conservan y siguen mostrando su nombre.
- Reseteo de contraseña por ADMIN: ya existe (`UpdateUserRequest.php:35`, `UserController.php:76-79`); solo verificar que en la UI quede claro ("Nueva contraseña, dejar vacío para no cambiar").
- Tests: usuario inactivo no puede loguear; sesión abierta se corta; no se puede desactivar al último ADMIN; ventas intactas tras desactivar.

## 9. Tests faltantes

Agregar en `tests/Feature/` (uno por regla, a través de HTTP):

- Venta con `total` manipulado → rechazada o recalculada.
- Venta con el mismo producto en dos líneas superando stock → error de validación, no 500.
- Venta de producto inactivo → rechazada.
- Anular venta ya anulada → no devuelve stock otra vez.
- Error de stock en `store` → redirige a `ventas.pos`.
- Degradar al último ADMIN → rechazado.
- Dashboard solo ADMIN; no exponer datos sensibles a otros roles ni mediante endpoints directos.
- Ventas: propiedad y anulación por rol; precio preservado en carrito y rechazo de manipulación o descuentos mayores al 50 %.
- Stock: estados 0, 1 y 2; desactivación a cero, activación con stock y restauración al anular.
- Acceso: cuenta desactivada, contraseña correcta o incorrecta, cierre de sesión y protección del último ADMIN.
- Dashboard: contadores reales, períodos en hora argentina y agrupación por categoría actual.
- Frontend: formularios React responsivos, confirmaciones y accesibilidad en los flujos migrados.
- "Ventas hoy" con venta a las 22:00 hora Argentina → cuenta en el día correcto.
- Borrar usuario con ventas → las ventas persisten.

Correr: `php artisan test --filter=VentaPosTest`, luego `composer test` y `npm run build`.

---

## Orden sugerido de trabajo

Orden orientativo; preservar cambios existentes. Ramas, commits, push y PR requieren autorización explícita del usuario.

1. **Ventas + datos** (secciones 1 y 2.1–2.3) — crítico, con tests de la sección 9. Revisar con `security-review` y, si querés doble revisión, `judgment-day`.
2. **Usuarios y permisos** (2.3, 12 y visibilidad por rol), incluida la navegación inicial.
3. **Dashboard y zona horaria** (4.1 y decisiones de dashboard).
4. **Frontend React completo y responsive** (5, 6 y 10), con pruebas de flujos por rol.
5. **Imágenes y preparación de producción** (11), con pruebas MySQL y respaldo.
6. **Eficiencia, portabilidad y documentación** (3, 4, 7 y 8), priorizando lo que se confirme necesario.

**Antes del primer deploy a Railway** tienen que estar hechos: 1 (ventas), 2.1 (borrado de cuentas y FKs), 4.1 (zona horaria), toda la sección 11 (incluida la migración de imágenes a MySQL) y la sección 12 (usuarios sin correo y desactivación).

## Decisiones tomadas

### Stock y activación de productos — 27/10/2026

- Un producto con stock mayor que cero debe estar activo; con stock igual a cero debe estar desactivado.
- Al desactivar manualmente un producto, su stock pasa a cero. Es solo un ajuste de stock: no registra una venta ni modifica los ingresos.
- Al activar un producto, se abre un cuadro para ingresar el stock y confirmar. Una cantidad mayor que cero activa el producto; cancelar no modifica los datos.
- Al reponer stock o restaurarlo al anular una venta, el producto vuelve a estar activo.
- Los datos actuales son de prueba. Al aplicar la regla, los productos existentes inactivos con stock positivo se activarán conservando su cantidad.

Estas decisiones están pendientes de implementación y reemplazan la lógica actual que permite productos inactivos con stock positivo.

### Anulación de ventas — 27/10/2026

- Una venta anulada debe dejar la operación como si no hubiera ocurrido: no debe contar como ingreso ni mantener descontado el stock.
- La anulación restaura las cantidades vendidas una sola vez, incluso ante solicitudes simultáneas; los productos con stock restaurado quedan activos.
- La venta anulada se conserva siempre en el historial, identificada como anulada, sin contar como ingreso.
- No se registra una devolución de dinero independiente: alcanza con conservar la venta como anulada.
- ADMIN puede anular cualquier venta; un usuario con rol Ventas solo puede anular sus propias ventas.

### Conservación y desactivación de cuentas — 27/10/2026

- Las cuentas nunca se borran: se conserva el usuario y su historial de ventas.
- Ningún usuario puede borrar ni desactivar su propia cuenta. Solo un ADMIN puede desactivar cuentas de otros usuarios.
- La base de datos debe impedir que se pierdan ventas o sus ítems por el borrado de usuarios o productos.
- Siempre debe existir al menos un ADMIN activo: no se puede desactivar ni quitar el rol al último ADMIN activo.
- Ningún ADMIN puede cambiar su propio rol; solo puede cambiar el rol de otros usuarios, respetando la protección del último ADMIN activo.
- Una cuenta desactivada no puede iniciar sesión. Mostrar que fue desactivada solo si la cuenta existe y la contraseña es correcta; para cuentas inexistentes o contraseñas incorrectas, mostrar un error genérico.
- Si se desactiva una cuenta con la sesión abierta, la sesión se cierra en su próxima interacción con el sistema.

### Dashboard — decisiones pendientes de implementación

- ADMIN tiene acceso a todo; solo ADMIN puede acceder al dashboard y a la administración de usuarios.
- Retirar la sección «Actividad reciente» y conservar el resumen y las acciones rápidas mostradas en la referencia del usuario.
- Verificar que todos los indicadores, listados y gráficos reflejen datos reales y actuales, sin datos simulados; mostrar los estados vacíos o de error cuando correspondan.
- La acción rápida «Nuevo Producto» debe abrir el mismo formulario emergente que se usa al crear un producto desde el inventario, en lugar de mostrar una vista nueva.
- Mostrar gráficos basados en datos reales de ventas por día para el período elegido y de ventas por categoría, comparando unidades vendidas para identificar las categorías más vendidas.
- Mantener sin cambios la posibilidad actual de editar la categoría de un producto; no agregar una restricción para impedirlo.
- Si un producto cambia de categoría, sus ventas anteriores también se cuentan en la categoría nueva del producto.
- Permitir filtrar los gráficos por día, semana y mes: hoy, los últimos 7 días y los últimos 30 días, respectivamente.
- Calcular los días de ventas y los períodos del dashboard según la hora de Argentina (`America/Argentina/Buenos_Aires`).
- Revisar la inconsistencia observada entre el contador de productos y el indicador de bajo stock. Actualmente el indicador de bajo stock usa el tamaño de una lista limitada a ocho resultados, no necesariamente el total; el umbral acordado es 1 (ver 6.6).
- Agrupar en una tarjeta de productos los indicadores de activos, agotados y bajo stock. «Bajo stock» cuenta únicamente productos con una unidad; cero unidades significa agotado. Los productos de bajo stock también están activos.
- Revisar los usos de la infraestructura de actividad antes de decidir si se elimina; retirar la tarjeta no implica borrar el historial ni otros registros.

### Visibilidad por rol — decisiones pendientes de implementación

- El usuario con rol Ventas solo puede ver sus propias ventas, no las de otros usuarios.
- Solo ADMIN puede ver el precio de compra y la ganancia de los productos; aplicar la restricción también en las respuestas del servidor, no solo en la interfaz.
- ADMIN puede acceder a todas las pantallas y funciones. Control Stock administra productos y categorías y sus operaciones, incluidas la carga, edición, modificación y desactivación de productos. Ventas se dedica únicamente a vender y a las operaciones de venta, incluida la anulación de sus propias ventas.
- Al ingresar, Ventas va por defecto al punto de venta y Control Stock al inventario de productos.

### Importes de ventas

- Los productos tienen un precio previamente cargado; los descuentos se aplican sobre ese precio. Por lo tanto, el total de una venta puede diferir de la suma de los precios de los productos.
- El descuento se aplica al subtotal de todos los productos y no puede superar el 50 % de ese subtotal. El total se calcula restando el descuento al subtotal.
- Si el precio de un producto cambia después de agregarlo al carrito, la venta conserva el precio que se mostraba en el carrito; el nuevo precio se aplica a las ventas siguientes. El servidor debe verificar el precio acordado sin confiar ciegamente en un importe enviado por el navegador.

### Frontend y experiencia

- Todo el frontend debe implementarse en React, incluidas las pantallas que todavía están en Blade, las notificaciones y los cuadros emergentes.
- El sistema y todos sus formularios deben ser responsivos para los distintos dispositivos.
- Las acciones que puedan implementarse en un cuadro emergente deben usarlo. Las acciones deben solicitar confirmación, por ejemplo al editar, desactivar o eliminar.

### Imágenes de productos

- Inicialmente las imágenes se almacenarán en MySQL. Se estima un catálogo de aproximadamente 300 productos, posiblemente 500; una alternativa más eficiente se evaluará después.

- 26/10/2026 — Umbral de bajo stock = **1** (0 Agotado, 1 Bajo). Aplica en 6.6.
- React es el frontend definitivo para todas las pantallas, incluidas autenticación, perfil y landing. Aplica en 8.3 y sección 10.
- 26/10/2026 — **Nadie puede borrar su propia cuenta**; las ventas realizadas no se pueden perder. Aplica en 2.1.a y 2.1.b.
- 26/10/2026 — Textos de la interfaz en **tuteo neutro profesional**. Aplica en 8.6 y 5.7.

- 26/10/2026 — Producción en **Railway con MySQL**. Aplica en sección 11 y reprioriza 4.2, 4.3, 4.5.

- 27/10/2026 — **Sin recuperación ni verificación por correo**; el ADMIN crea, edita, resetea y desactiva usuarios. Aplica en sección 12 y 11.7; reemplaza 2.5.
- 27/10/2026 — **Imágenes de productos en MySQL**, con Cloudflare R2 como alternativa. Aplica en 11.1.a y 11.1.b.

## Decisiones abiertas

- Las decisiones de negocio principales están definidas. Resolver durante el diseño los detalles técnicos de verificación segura del precio del carrito, migración y pruebas; consultar al usuario si surge una decisión de negocio nueva.
