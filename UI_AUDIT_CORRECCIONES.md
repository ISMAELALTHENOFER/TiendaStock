# Auditoría visual y funcional — TiendaStock

Fecha: 2026-09-11  
Entorno revisado: `http://127.0.0.1:8000`  
Usuario utilizado: `testuser` (la contraseña no se registra)

## Resumen

La aplicación carga correctamente y las rutas principales son accesibles. Los problemas más visibles son:

1. No existe una acción visible para cerrar sesión.
2. Las tablas de Productos, Usuarios y Ventas tienen muy poca jerarquía visual: encabezados, filas y acciones se perciben como texto plano.
3. Varias acciones de fila se muestran como `Ver`, `Editar` o `⋮` sin suficiente contexto accesible ni diferenciación visual.
4. El menú de usuario del encabezado muestra el nombre, pero no ofrece un menú de cuenta ni logout.

## Correcciones prioritarias

### P0 — Cerrar sesión

- Agregar una acción visible **Cerrar sesión** en el menú de usuario del encabezado.
- Mantener el flujo estándar de Laravel: `POST /logout`, token CSRF y redirección a `/login`.
- En desktop, mostrar el nombre/avatar como un menú desplegable con:
  - nombre de usuario;
  - rol;
  - `Cerrar sesión`.
- En mobile, incluir la misma acción dentro del drawer de navegación o del menú de cuenta.
- Agregar `aria-label="Cerrar sesión"` y un estado de foco visible.
- Validar que cerrar sesión invalide la sesión y que el botón Atrás del navegador no exponga contenido autenticado.

### P0 — Jerarquía visual de listas

Aplicar el mismo patrón visual a Productos, Usuarios y Ventas:

- Encabezado de tabla con fondo contrastante, texto pequeño semibold, uppercase y separación suficiente.
- Filas con `border-bottom`, hover sutil y alineación vertical consistente.
- Diferenciar visualmente columnas numéricas y monetarias mediante alineación a la derecha.
- Mantener una fila claramente legible cuando hay muchos registros.
- Usar estados de fila coherentes: hover, focus-within, deshabilitado y seleccionado si aplica.
- Aumentar la separación entre contenido y acciones para que la última columna no parezca pegada.
- En mobile, conservar las tarjetas actuales, pero mejorar la jerarquía: título, metadatos, estado y acciones como bloques separados.

## Productos

Observado en `/productos`:

- La tabla tiene 15 filas y 10 columnas, pero el encabezado y las filas carecen de una jerarquía visual fuerte.
- Las acciones `Ver`, `Editar` y `Desactivar` aparecen como enlaces/botones genéricos repetidos.
- El control de selección no tiene un nombre accesible identificable.
- Los `select` de categoría, talle y color no exponen un `aria-label` propio en el DOM revisado.
- Las opciones de filtros parecen contener valores de prueba o datos de desarrollo (`categoria nueva`, `producto`, `prueba`, `seguro`, `2`).

Correcciones:

- Añadir labels visibles o `aria-label` a cada filtro y al checkbox de selección.
- Convertir acciones repetidas en botones/enlaces con contexto, por ejemplo `Editar Buzo`, `Ver Buzo` y `Desactivar Buzo`.
- Aplicar badges para stock y margen: stock bajo, sin stock y margen negativo deben destacarse.
- Añadir confirmación con nombre del producto antes de desactivar.
- Revisar si los registros de prueba deben ocultarse o limpiarse antes de producción.
- Mantener la tabla dentro de un contenedor con scroll horizontal intencional y comunicarlo a lectores de pantalla si corresponde.

## Usuarios

Observado en `/admin/users`:

- La tabla funciona, pero se visualiza plana y sin separación fuerte entre registros.
- La columna de rol debería utilizar un badge de estado más visible.
- La acción `Editar` no incluye el nombre del usuario en su nombre accesible.
- En las tarjetas mobile, los datos aparecen concatenados visualmente (`isma@ ismaAdmin...`), lo que dificulta el escaneo.

Correcciones:

- Separar nombre, usuario, email y rol en bloques con labels secundarios.
- Aplicar badge consistente para `Administrador`, `Ventas` y `Control Stock`.
- Usar acciones contextuales: `Editar usuario isma`.
- Añadir estado vacío, estado de error y feedback posterior a crear/editar.
- Revisar si debe existir búsqueda o filtro por rol cuando aumente la cantidad de usuarios.

## Ventas

Observado en `/ventas`:

- La tabla de historial tiene 8 columnas y depende del overflow horizontal en tabletas.
- Las acciones `⋮` no tienen nombre accesible visible en la extracción del DOM.
- Las acciones de cancelar aparecen repetidas como `Cancelar venta`, sin identificador de la venta.
- Los filtros `Desde`, `Hasta` y `Estado` no exponen labels accesibles propios en el DOM revisado.

Correcciones:

- Reemplazar `⋮` por un botón con `aria-label="Acciones de venta #13"` y tooltip visible.
- Nombrar cada cancelación como `Cancelar venta #13`.
- Mantener `Ver` como `Ver venta #13` para accesibilidad y claridad.
- Añadir labels asociados a las fechas y al selector de estado.
- Mostrar un resumen de filtros activos y una acción clara para limpiarlos.
- En mobile, priorizar total, fecha, estado y acciones; dejar items, entrega y procesado como metadatos secundarios.
- Confirmar la cancelación indicando el impacto en stock antes de enviar.

## Dashboard

Observado en `/dashboard`:

- El encabezado muestra `Test User`, pero no hay menú de cuenta ni logout.
- Las métricas cargan, pero deben tener una diferencia visual suficiente entre valor, etiqueta y tendencia.
- Las tarjetas de acciones rápidas necesitan distinguir mejor la acción primaria (`Nueva Venta`) de las secundarias.
- La actividad reciente y la analítica deben conservar estados de carga, vacío y error visualmente diferenciados.

Correcciones:

- Incorporar el menú de cuenta y logout del apartado P0.
- Añadir contexto temporal a métricas y gráficos cuando exista.
- Mantener una sola acción primaria por bloque y botones secundarios con menor peso visual.
- Verificar que los gráficos tengan alternativa textual accesible.

## Navegación y layout

- En el drawer mobile, verificar que el usuario pueda llegar a logout sin depender del encabezado.
- Añadir un indicador visual de ruta activa que sea perceptible sin depender únicamente del color.
- Revisar que el ancho colapsado de la sidebar mantenga tooltips o labels accesibles para sus iconos.
- Asegurar que el nombre de la aplicación y el usuario no queden truncados en anchos intermedios.
- Mantener foco visible en links, botones, inputs, selects y acciones de tabla.

## Formularios y feedback

- Todos los campos deben tener label asociado, no solo placeholder.
- Los errores `422` deben aparecer junto al campo y anunciarse de forma accesible.
- Los mensajes de éxito/error deben poder leerse con tecnología asistiva (`role="status"` o `role="alert"` según el caso).
- Los botones deben indicar estado de envío y evitar doble submit.
- Los formularios de crear/editar deben conservar claramente la diferencia entre cancelar, guardar y eliminar.

## Responsive y accesibilidad

Verificar en 320, 390/412, 768, 1024, 1440 y 1600 px:

- Sin overflow horizontal del `body`.
- Sin clipping de focus rings.
- Targets táctiles de al menos 44 px.
- Tab order lógico.
- `Escape` cierra drawer, modal y menús.
- El foco vuelve al elemento que abrió drawer/modal/menu.
- Respeto de `prefers-reduced-motion`.
- Tablas con scroll intencional o tarjetas completas, nunca contenido esencial oculto.
- Textos largos, emails y nombres extensos sin romper el layout.

## Print

- Mantener ocultos navegación, acciones y controles al imprimir una venta.
- Mantener visible únicamente el recibo y la información necesaria.
- Verificar que el resultado impreso no dependa de colores para comunicar estado.

## Datos de prueba y preparación para producción

- Revisar nombres y categorías de prueba visibles en Productos y Categorías.
- Confirmar que las fechas y monedas se muestran con el locale esperado en todas las pantallas.
- Confirmar pluralización: por ejemplo, `1 producto` en lugar de `1 producto(s)`.
- Definir el estado visual para listas vacías, errores de red y permisos insuficientes.

## Orden recomendado de implementación

1. Logout y menú de cuenta responsive.
2. Jerarquía visual compartida para tablas, filas y acciones.
3. Labels y nombres accesibles contextuales en Productos, Usuarios y Ventas.
4. Mejora de tarjetas mobile y estados de stock/rol/venta.
5. Revisión de datos de prueba, pluralización y locale.
6. Prueba manual completa de responsive, teclado y print.

## Evidencia del relevamiento

- `/dashboard`: autenticación correcta; no se encontró botón, link ni formulario de logout en el DOM.
- `/productos`: tabla de 10 columnas, 15 registros visibles, filtros sin labels accesibles propios y acciones repetidas sin contexto de registro.
- `/categorias`: tarjetas con estilos base correctos; conviene reutilizar ese nivel de jerarquía en las tablas.
- `/admin/users`: tabla y tarjetas mobile funcionales, pero con baja separación visual y acciones no contextuales.
- `/ventas`: tabla de 8 columnas, acciones `⋮` sin nombre accesible detectado y botones de cancelar repetidos por fila.
- A 390 px, la página no presentó overflow horizontal del `body`; Ventas mostró correctamente la alternativa de tarjetas mobile.

## Pendiente de validación específica

No se modificó código en esta auditoría. Antes de implementar, conviene recorrer también `/ventas/pos`, el detalle de venta y los formularios de crear/editar para confirmar que no existan problemas adicionales fuera de las listas revisadas.
