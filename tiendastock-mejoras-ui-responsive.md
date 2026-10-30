# TiendaStock — Especificación de mejoras UI/UX y responsive

> **Objetivo para la IA implementadora:** modernizar y unificar las pantallas de **Productos (`/productos`)**, **Categorías (`/categorias`)** y **Ventas (`/ventas`)** tomando como referencia las capturas entregadas. Implementar una experiencia **responsive de extremo a extremo**, conservando la identidad actual (sidebar azul petróleo, verde de acciones principales, fondos claros, superficies blancas y estados coloreados). **No modificar reglas de negocio, contratos de API, backend ni comportamiento funcional existente.**

## 0. Procedimiento obligatorio antes de modificar código

1. Inspeccionar la estructura real del repositorio: stack de frontend, rutas, componentes React, layouts, estilos y librerías instaladas. El proyecto usa Laravel + React; **verificar la implementación efectiva** y no asumir rutas de archivos ni bibliotecas.
2. Localizar las vistas de Productos, Categorías y Ventas, sus servicios/hooks, componentes compartidos, estilos y la implementación de los menús/modales existentes.
3. Documentar brevemente el estado inicial y los componentes reutilizables disponibles. Reutilizar componentes y convenciones del proyecto; evitar introducir otra librería de UI si no es necesaria.
4. Conservar los estados, permisos, filtros, paginación, formularios, validaciones, acciones y navegación existentes. **No eliminar opciones ni cambiar su disponibilidad funcional**; solo reorganizar la presentación.
5. Implementar en cambios pequeños y comprobables. Si surge una modificación funcional imprescindible, informarla antes de aplicarla.
6. No realizar migraciones de datos ni ajustes del backend. No reescribir Blade o módulos ajenos a este alcance.

## 1. Diagnóstico de las capturas

### Aspectos que ya funcionan y deben mantenerse
- Identidad visual consistente: navegación lateral oscura, selección de menú verde, botones principales verdes.
- Títulos claros, tarjetas blancas con bordes tenues, badges de estados, campos con esquinas redondeadas.
- Acciones, búsqueda/filtros y paginación presentes.
- Vista de Categorías mediante tarjetas: patrón adecuado en escritorio.

### Problemas observados
- **Texto y controles pequeños** para lectura cómoda; la tabla concentra demasiada información con letra diminuta.
- **Productos:** columna Imagen presenta guiones/placeholder poco informativo; filas muy extensas; tres acciones textuales consumen espacio; muchos productos llamados «Buzo» son difíciles de distinguir visualmente.
- **Ventas:** desproporción entre espacios de columnas; número, fecha, importe y acciones podrían tener una jerarquía más clara; barra de filtros consume demasiado alto.
- **Categorías:** tarjetas con bastante espacio vacío, acciones poco jerarquizadas y textos «No se puede eliminar» que compiten con acciones activas.
- **Conjunto:** estilos de filtros y acciones no completamente unificados entre pantallas; no se evidencia el comportamiento móvil en las capturas (debe implementarse y probarse, no asumir que ya existe).
- **Paginación y pie de listas:** tienen que conservarse visibles y utilizables en pantallas estrechas sin desbordes.

## 2. Sistema visual compartido (sin rediseño de marca)

- Reutilizar valores cromáticos reales del proyecto (extraer tokens existentes; **no reemplazar colores de marca arbitrariamente**).
- Crear o consolidar variables/tokens para: `color-bg`, `color-surface`, `color-text`, `color-muted`, `color-border`, `color-primary`, `color-sidebar`, `color-success`, `color-warning`, `color-danger`, `radius`, `shadow`, `spacing`.
- Tipografía de contenido de tabla: **13–14 px como objetivo en escritorio**, con encabezados 11–12 px y contraste suficiente. Evitar texto de 9–10 px como patrón habitual.
- Controles interactivos: objetivo táctil **mínimo 44 × 44 px en móvil**, indicadores de foco visibles, hover moderado; estados no diferenciados únicamente por color.
- Bordes sutiles en lugar de divisores saturados, altura de fila escritorio recomendada de **52–60 px** (ajustar en pruebas reales), sombreado muy discreto en contenedores.
- Títulos, subtítulos, tarjetas, badges, botones, inputs, selects y paginación con alineaciones y espaciados uniformes.
- Header de tabla puede conservar azul oscuro del sistema; encabezados estables y fáciles de leer. Alinear números a la derecha con `font-variant-numeric: tabular-nums`.
- Aplicar estados de interacción consistentes: `hover`, `focus-visible`, `active`, `disabled`, `loading`, `empty` y `error`.
- **No convertir filas en «seleccionadas» por hover**: usar un hover tenue; reservar el fondo de selección para selección real.

## 3. Arquitectura de interfaz reutilizable

Explorar la reutilización de componentes existentes y, si aporta valor, extraer:

- `PageHeader`: título, descripción, acción principal y acciones secundarias.
- `FilterPanel`: campos con etiquetas, botón limpiar y comportamiento adaptable.
- `DataTable` / `ResponsiveDataList`: cabeceras, filas, estados, carga, vacío, errores y variante móvil.
- `StatusBadge`: stock, estado de venta, recuento por categoría (variantes distintas pero estilo coherente).
- `RowActions`: acción primaria visible y menú `…` para secundarias **manteniendo permisos, confirmaciones y accesibilidad**.
- `Pagination`: total mostrado, páginas, anterior/siguiente y cantidad por página.
- `EmptyState`, `LoadingSkeleton`, `ErrorState`.

**Regla:** priorizar composición y extracción incremental; evitar crear un componente genérico complejo que dificulte el mantenimiento. Las páginas pueden conservar layouts específicos para sus datos.

## 4. Productos (`/productos`)

### Escritorio
- Mantener el título **Inventario de Productos**, subtítulo, `Ver inactivos` y `Nuevo Producto`.
- Distribuir buscador y filtros (categoría, talle, color) sobre un panel consistente, con etiquetas alineadas. Mantener comportamiento real de búsqueda y selección.
- Dejar la tabla con: **Imagen | Nombre | Categoría | Talle | Color | P. compra | P. venta | Ganancia | Stock | Acciones**. No retirar información requerida.
- Imagen: miniatura 40–44 px con `object-fit: cover`; si no existe, ícono neutro y texto accesible, **nunca un guion dentro de un cuadrado que parezca botón**.
- Nombre del producto más legible y prominente. Opcionalmente una línea de detalle solo si proviene de datos reales; **no inventar SKU o identificadores**.
- Categoría en badge neutro; talle/color con textos cortos. Los guiones de campos no informados deben verse como dato faltante y no como control.
- Precios y ganancia a la derecha, formato monetario actual del sistema preservado. Ganancia con valor legible (verde para positiva, tratamiento neutro para cero y negativo si existe).
- Stock: badge con cantidad y etiqueta **Bajo/Agotado** según la lógica actual; no redefinir umbrales de negocio. Validar que se muestren correctamente cantidades de `0`, bajo stock y stock normal.
- Acciones: `Ver` fácilmente accesible; mover `Editar` y `Desactivar` a menú de opciones si mejora la densidad. **Conservar confirmación de desactivación, permisos y acciones existentes**. Si el estado inactivo habilita otra acción, mostrar la opción correspondiente según lógica vigente.
- Revisar anchuras: nombre/categoría deben tener espacio útil; columnas monetarias y stock con ancho estable; evitar que Acciones desplace contenidos.

### Móvil
- **No reducir toda la tabla hasta que sea ilegible**. Preferir una vista tipo tarjeta/fila compacta por producto, compartiendo la misma fuente de datos y acciones.
- Cada registro debe mostrar: miniatura, nombre, categoría/talle/color, precio de venta, stock y acceso a detalles/acciones.
- Precio de compra y ganancia accesibles mediante detalle expandible o vista `Ver`, sin perder su disponibilidad. Si se mantiene tabla en tablet, utilizar scroll horizontal **solo dentro del contenedor** con pista visual de desplazamiento.
- Buscador al 100% del ancho; filtros en una o dos columnas según espacio, o sección desplegable identificada como **Filtros** con indicador de filtros activos; botón para limpiar sin perder la funcionalidad.

## 5. Ventas (`/ventas`)

### Escritorio
- Mantener **Historial de Ventas**, subtítulo y `Nueva Venta`.
- Normalizar panel de filtros con búsqueda, `Desde`, `Hasta`, `Estado`, `Filtrar` y `Limpiar`; mantener semántica actual de filtros (no implementar filtrado instantáneo si actualmente requiere confirmación, salvo decisión expresa).
- Tabla con todos los campos funcionales: **# | Fecha | Ítems | Total | Entrega | Procesado por | Estado | Acciones**.
- Formato de fecha compacto y consistente, respetando datos originales, hora y zona aplicable. Evitar abreviaturas extensas de AM/PM cuando sea posible sin perder precisión.
- Destacar número de venta e importe total (moneda a derecha, números tabulares). Mostrar cantidad de ítems legible.
- Entrega con texto consistente, e ícono opcional si ya existe en el catálogo de íconos.
- Estados **Completada / Anulada** en badges claros. Conservar otros estados reales, si existen.
- `Ver` como acción principal y menú contextual para las opciones adicionales ya existentes; una venta anulada no debe ofrecer operaciones prohibidas.

### Móvil
- Tarjetas compactas por venta con: `# de venta`, fecha/hora, importe, badge de estado, cantidad de ítems y entrega.
- Usuario procesador visible como detalle secundario o en expansión, sin ocultarlo definitivamente.
- Filtros en una columna; fecha Desde/Hasta con controles completos, accesibles y ancho total; botones **Filtrar** y **Limpiar** apilados o en una fila solo si entran cómodamente.
- Respetar el comportamiento de paginación y el menú de acciones.

## 6. Categorías (`/categorias`)

- Conservar **Categorías de Productos**, botón `Nueva Categoría` y los indicadores **Total categorías**, **Productos asignados**, **Categorías vacías**.
- Revisar la jerarquía de las tarjetas de métricas: etiqueta secundaria, número destacado e ícono consistente.
- Mantener búsqueda de categorías y comportamiento del botón **Buscar**.
- Tarjetas con nombre, descripción y badge de cantidad de productos; reducir espacio muerto con `min-height` razonable y distribución por contenido. No forzar la misma altura si se produce un vacío excesivo.
- Acciones `Ver` y `Editar` accesibles. Si `Eliminar` no está permitido porque hay productos relacionados, mostrar texto auxiliar discreto o una acción deshabilitada **con explicación accesible**; no simular un botón activo.
- Conservar reglas de eliminación y confirmaciones.
- Escritorio: **3 columnas cuando el ancho útil lo permita**; tablet: 2; móvil: 1.
- Tarjetas de métricas: 3/2/1 columnas según ancho disponible y legibilidad.
- En móvil, búsqueda a ancho completo; evitar botón Buscar diminuto junto a un input excesivamente estrecho.

## 7. Navegación y layout global responsive

- Sidebar izquierda **persistente en escritorio** con ancho comparable al actual.
- En tablet/móvil, convertir sidebar en **drawer accesible** con botón de menú en el header; fondo superpuesto, cierre con `Escape`, botón Cerrar, navegación que cierra el drawer y gestión correcta del foco.
- Respetar encabezado superior con usuario/rol, evitando solapamiento con títulos y botones.
- El área principal debe ocupar el ancho disponible con `min-width: 0`; **ninguna pantalla debe producir scroll horizontal global**.
- Los encabezados de página deben permitir wrap: título y descripción arriba y acción principal debajo en móvil cuando no entren en una fila.
- Evitar alturas fijas que corten contenidos. Compatible con zoom del navegador a 200%.
- Respetar márgenes/padding uniformes y `safe-area` cuando corresponda.

## 8. Breakpoints y reglas de adaptación

Usar el sistema de breakpoints ya existente si lo hay. Guía inicial (ajustar según contenido real):

| Ancho | Comportamiento esperado |
|---|---|
| `>= 1280px` | Sidebar visible; tablas completas; filtros distribuidos; categorías 3 columnas. |
| `1024–1279px` | Sidebar según capacidad; tabla con columnas prioritarias y/o scroll interno; filtros en 2 columnas; categorías 2–3 columnas. |
| `768–1023px` | Sidebar colapsable/drawer; filtros en 2 columnas; tablas con scroll interno o disposición simplificada; categorías 2 columnas. |
| `<= 767px` | Drawer; encabezados apilados; registros de productos/ventas en tarjetas compactas; filtros en 1 columna; categorías 1 columna. |
| `<= 375px` | Sin recorte de botones o importes; acciones al alcance; paginación adaptada; no desbordes horizontales. |

**No esconder datos críticos de manera irreversible.** Si alguna columna desaparece de la vista compacta, su información debe quedar disponible en expansión, detalle o equivalente ya existente.

## 9. Paginación, estados y accesibilidad

- Unificar diseño y ubicación de paginación en las tres vistas.
- Mantener conteo («Mostrando X–Y de Z»), página actual, anterior/siguiente y selector por página con comportamiento actual.
- Móvil: permitir que el conteo pase a una línea, botones de navegación a otra y selector se reacomode; no reducir clicables a tamaños inaccesibles.
- Preservar filtros al navegar entre páginas cuando así funcione actualmente; asegurar que cambios de filtros y cantidad por página gestionen la página actual correctamente.
- Agregar/estandarizar estados: cargando, sin datos, sin resultados para filtros y error recuperable; no sustituir mensajes existentes que expresen reglas de negocio.
- Contraste WCAG AA como referencia; foco visible, labels asociadas, nombres accesibles para íconos y botones de acciones, `aria-expanded` para menús/filtros, navegación por teclado, orden lógico de tabulación.
- No depender exclusivamente del color para comunicar el estado (acompañar con texto).
- Modal y menú emergente no deben quedar fuera de pantalla ni detrás del sidebar.

## 10. Requisitos técnicos / restricciones

- Implementar con la arquitectura existente **Laravel + React**, sin incorporar Inertia ni migrar vistas como parte de este trabajo.
- Reutilizar estilos y componentes existentes; estilos aislados o tokens comunes, evitando reglas globales que rompan Dashboard, Usuarios, login u otras páginas.
- Mantener contratos API, rutas, validaciones, permisos, formato persistido, cálculos, estado de filtros y funcionalidad de los botones.
- No agregar dependencias sin justificar su necesidad y verificar compatibilidad con el proyecto.
- Evitar duplicación de lógica entre tabla desktop y tarjetas mobile: compartir fuente de datos y callbacks; usar CSS responsive o composición simple.
- No modificar datos ni inventar campos para mejorar la estética.
- Controlar desbordes de textos largos, nombres, importes y categorías (`overflow-wrap`, truncado con forma de acceder al valor completo cuando aplique).
- Formatear números/moneda/fechas de acuerdo con las convenciones existentes de Argentina y las reglas reales de la aplicación.

## 11. Plan de implementación sugerido

### Fase A — Relevamiento
- [ ] Identificar rutas, componentes, estilos, UI kit y comportamiento actual en las tres pantallas.
- [ ] Identificar reglas de permisos/estados para acciones, filtros, orden, paginación y umbrales de stock.
- [ ] Listar archivos afectados y riesgos de regresión.

### Fase B — Base compartida
- [ ] Unificar tokens visuales, tipografía, inputs, botones, badges, paneles, estados y paginación.
- [ ] Implementar sidebar/drawer y layout principal responsive.
- [ ] Revisar focus/hover y estados de carga/error/vacío.

### Fase C — Pantallas
- [ ] Productos: mejor jerarquía, imágenes placeholder, stock y acciones; lista móvil.
- [ ] Ventas: mejor jerarquía, filtros, estados, acciones; lista móvil.
- [ ] Categorías: métricas, tarjetas, acciones y rejilla adaptable.

### Fase D — Validación
- [ ] Ejecutar lint, build y pruebas disponibles.
- [ ] Probar visualmente 320, 375, 390, 768, 1024, 1280 y 1440 px.
- [ ] Revisar zoom 200%, teclado y foco, menú móvil, filtros y modales.
- [ ] Probar productos activos/inactivos, stock 0/bajo/normal, imágenes ausentes, nombres largos y precios grandes.
- [ ] Probar ventas completadas/anuladas, filtrado por rango de fechas, búsqueda, menú de acciones y permisos.
- [ ] Probar categorías vacías/con productos, búsqueda, edición y eliminación permitida/prohibida.
- [ ] Comprobar paginación, cantidad por página, resultados vacíos y conservación de datos.

## 12. Criterios de aceptación obligatorios

1. Las tres páginas comparten una identidad visual coherente con el diseño actual de TiendaStock.
2. En móvil la interfaz es utilizable sin zoom manual y sin **scroll horizontal del documento**.
3. Sidebar, header, filtros, tablas/tarjetas, métricas, acciones y paginación son completamente responsive.
4. Ninguna acción, dato relevante, validación, permiso o comportamiento de negocio desaparece.
5. Todas las operaciones se pueden completar con mouse, pantalla táctil y teclado cuando aplique.
6. La pantalla de escritorio muestra datos de forma legible, sin el texto excesivamente pequeño observado en las capturas.
7. Los cambios no degradan otras áreas de la aplicación.
8. Build y pruebas existentes pasan, o se informan con detalle los problemas preexistentes.
9. Entregar resumen de cambios, archivos afectados, evidencia de pruebas responsive y cualquier punto pendiente.

## 13. Instrucción final para la IA

**Implementá esta especificación directamente en el código existente, después de inspeccionarlo. No hagas una maqueta independiente ni un rediseño sin conexión con el proyecto.** Conservá el aspecto e identidad actual y mejorá espaciado, jerarquía, legibilidad, componentes y experiencia responsive. No cambies lógica de negocio. Si una mejora propuesta entra en conflicto con un comportamiento real, priorizá la funcionalidad existente y documentá la decisión. Al finalizar, informá qué archivos cambiaste, cómo probaste los tamaños de pantalla y qué quedó pendiente.
