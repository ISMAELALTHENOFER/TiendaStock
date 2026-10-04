# Rediseño del Dashboard --- TiendaStock

## Objetivo

Rediseñar el **Dashboard de TiendaStock** para convertirlo en una
interfaz moderna, profesional, responsive y realmente útil para la
gestión diaria del negocio.

Se debe conservar la funcionalidad existente y mejorar principalmente:

-   Jerarquía visual.
-   Navegación.
-   Sidebar.
-   KPIs.
-   Acciones rápidas.
-   Analítica.
-   Actividad reciente.
-   Información de stock.
-   Responsive.
-   Estados de carga, error y vacío.
-   Consistencia con el nuevo diseño del login.

> Importante: el **sidebar lateral debe ocupar siempre el 100% del alto
> visible de la pantalla** en desktop, independientemente de la cantidad
> de contenido del dashboard.

------------------------------------------------------------------------

# 1. Principios de diseño

La interfaz debe transmitir:

-   Profesionalismo.
-   Claridad.
-   Simplicidad.
-   Rapidez.
-   Sensación de producto SaaS moderno.
-   Consistencia visual.

Evitar:

-   Sobrecarga de información.
-   Colores innecesarios.
-   Sombras fuertes.
-   Cards excesivamente grandes.
-   Espacios vacíos sin propósito.
-   Elementos visuales genéricos de Laravel.
-   Información redundante.

Utilizar el mismo sistema visual definido para el login de TiendaStock.

------------------------------------------------------------------------

# 2. Layout general

En desktop utilizar la siguiente estructura:

``` text
┌───────────────┬──────────────────────────────────────────────┐
│               │ Header                                       │
│               ├──────────────────────────────────────────────┤
│               │                                              │
│   SIDEBAR     │ Dashboard                                    │
│               │                                              │
│   100dvh      │ KPIs                                         │
│               │                                              │
│               │ Analítica / Stock / Actividad                │
│               │                                              │
│               │                                              │
└───────────────┴──────────────────────────────────────────────┘
```

El sidebar y el contenido principal deben ser elementos independientes.

------------------------------------------------------------------------

# 3. Sidebar --- requisito principal

El sidebar actual debe mejorarse visualmente y ocupar **todo el alto de
la pantalla**.

No debe finalizar cuando terminan las opciones del menú.

Debe verse de esta forma:

``` text
┌──────────────────┐
│ TiendaStock      │
│                  │
│ WORKSPACE        │
│ Dashboard        │
│                  │
│ OPERACIÓN        │
│ Ventas           │
│ Productos        │
│ Categorías       │
│                  │
│ ADMINISTRACIÓN   │
│ Usuarios         │
│                  │
│                  │
│                  │
│                  │
│──────────────────│
│ Usuario          │
│ @usuario         │
└──────────────────┘
        ↑
       100dvh
```

## Implementación esperada

Preferir:

``` css
.sidebar {
    position: fixed;
    top: 0;
    left: 0;

    width: 260px;
    height: 100dvh;

    display: flex;
    flex-direction: column;
}
```

Como fallback:

``` css
min-height: 100vh;
```

La zona de navegación debe crecer:

``` css
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
}
```

La información del usuario debe permanecer abajo:

``` css
.sidebar-footer {
    margin-top: auto;
}
```

El contenido principal debe compensar el ancho del sidebar:

``` css
.main-content {
    margin-left: 260px;
    min-height: 100dvh;
}
```

No utilizar alturas hardcodeadas basadas en el contenido.

------------------------------------------------------------------------

# 4. Branding del sidebar

Reemplazar cualquier branding genérico por:

``` text
[ isotipo ] TiendaStock
```

El mismo isotipo utilizado en el login debe reutilizarse aquí.

El nombre debe tener buena legibilidad pero sin ocupar demasiado
espacio.

------------------------------------------------------------------------

# 5. Navegación

Mantener las categorías:

``` text
WORKSPACE
Dashboard

OPERACIÓN
Ventas
Productos
Categorías

ADMINISTRACIÓN
Usuarios
```

Cada opción debe tener:

-   Ícono.
-   Texto.
-   Estado normal.
-   Hover.
-   Active.
-   Focus-visible.

Ejemplo:

``` text
▦  Dashboard
🛒 Ventas
▧  Productos
▣  Categorías
👥 Usuarios
```

Los íconos deben provenir de una única librería o sistema visual.

No mezclar diferentes estilos de íconos.

------------------------------------------------------------------------

# 6. Estado activo

La opción seleccionada debe ser claramente reconocible.

Ejemplo:

``` css
.sidebar-item.active {
    background: rgba(...);
    font-weight: 600;
}
```

Puede utilizarse además una pequeña marca lateral con el color
principal.

Evitar contrastes excesivos.

------------------------------------------------------------------------

# 7. Sidebar colapsable

En desktop permitir opcionalmente reducir:

``` text
260px
```

a aproximadamente:

``` text
72px
```

Modo expandido:

``` text
[▦] Dashboard
[🛒] Ventas
[▧] Productos
```

Modo reducido:

``` text
[▦]
[🛒]
[▧]
```

Cuando esté reducido:

-   Mostrar tooltip al hacer hover.
-   Mantener accesibilidad.
-   Conservar el estado activo.
-   No perder funcionalidad.

Esta funcionalidad es deseable, pero no debe complicar innecesariamente
la implementación inicial.

------------------------------------------------------------------------

# 8. Sidebar responsive

En tablet/mobile el sidebar no debe permanecer fijo ocupando espacio
horizontal.

Debe convertirse en un **drawer lateral**.

``` text
☰  TiendaStock
```

Al abrir:

``` text
┌─────────────────────┐
│ TiendaStock      ×  │
│                     │
│ Dashboard           │
│ Ventas               │
│ Productos            │
│ Categorías           │
│ Usuarios             │
│                     │
│                     │
│─────────────────────│
│ Usuario              │
└─────────────────────┘
```

Agregar overlay sobre el contenido.

Debe cerrarse mediante:

-   Botón X.
-   Click fuera.
-   Tecla Escape.
-   Selección de una opción cuando corresponda.

Bloquear el scroll del `body` mientras el drawer esté abierto.

------------------------------------------------------------------------

# 9. Header

El header actual tiene demasiado espacio vacío.

Simplificarlo.

Desktop:

``` text
                                           [ Usuario ▼ ]
```

Mobile:

``` text
☰   TiendaStock                            [ Usuario ]
```

El header puede ser sticky:

``` css
position: sticky;
top: 0;
z-index: ...;
```

Debe tener:

-   Fondo blanco.
-   Borde inferior sutil.
-   Altura consistente.
-   Menú de usuario.

------------------------------------------------------------------------

# 10. Menú de usuario

Al hacer click en el usuario:

``` text
┌─────────────────────┐
│ Test User           │
│ @testuser           │
│─────────────────────│
│ Mi perfil           │
│ Cerrar sesión       │
└─────────────────────┘
```

No agregar opciones que todavía no existan funcionalmente salvo que se
implementen correctamente.

------------------------------------------------------------------------

# 11. Encabezado del Dashboard

Mantener:

``` text
OVERVIEW

Dashboard
Visualizá el estado general de tu negocio.
```

Se recomienda cambiar:

``` text
Visualiza el estado general de tu inventario.
```

por:

``` text
Visualizá el estado general de tu negocio.
```

porque el sistema contempla más que inventario.

------------------------------------------------------------------------

# 12. KPIs principales

Priorizar métricas realmente útiles.

Propuesta:

``` text
┌─────────────────┐
│ Ventas hoy      │
│ $125.500        │
│ 8 ventas        │
└─────────────────┘

┌─────────────────┐
│ Ventas del mes  │
│ $1.840.300      │
│ +12%            │
└─────────────────┘

┌─────────────────┐
│ Productos       │
│ 342             │
│ 310 activos     │
└─────────────────┘

┌─────────────────┐
│ Bajo stock      │
│ 12              │
│ Ver productos → │
└─────────────────┘
```

Si todavía no existen datos suficientes, conservar inicialmente:

-   Total productos.
-   Categorías.
-   Productos activos.
-   Valor total.

Pero diseñar los componentes para poder evolucionarlos.

------------------------------------------------------------------------

# 13. Diseño de KPI Card

Cada KPI debe incluir:

-   Label.
-   Valor principal.
-   Información secundaria.
-   Ícono discreto.
-   Opcionalmente tendencia.

Ejemplo:

``` text
Ventas del mes                    $
$1.840.300
↑ 12% respecto al mes anterior
```

No utilizar colores fuertes en toda la card.

Los colores semánticos deben reservarse para:

-   Positivo.
-   Advertencia.
-   Error.
-   Información.

------------------------------------------------------------------------

# 14. Acciones rápidas

Mantener esta sección porque es útil.

Acciones:

``` text
Nueva Venta
Nuevo Producto
Nueva Categoría
Nuevo Usuario
```

`Nueva Venta` debe continuar siendo la acción primaria.

Ejemplo:

``` text
[ + Nueva Venta ] [ + Producto ] [ + Categoría ] [ + Usuario ]
```

El resto debe utilizar estilo secundario.

En mobile deben adaptarse:

``` text
[ Nueva Venta      ]
[ Nuevo Producto   ]
[ Nueva Categoría  ]
[ Nuevo Usuario    ]
```

o utilizar grid de dos columnas cuando haya espacio suficiente.

------------------------------------------------------------------------

# 15. Analítica de ventas

La sección actual basada solamente en texto debe evolucionar hacia una
visualización gráfica.

Agregar:

``` text
Ventas últimos 7 días
```

Ejemplo conceptual:

``` text
$50k ┤                 ╭─
$40k ┤          ╭──────╯
$30k ┤     ╭────╯
$20k ┤ ╭───╯
$10k ┼─╯
     Lun Mar Mié Jue Vie Sáb Dom
```

Permitir cambiar período:

``` text
[ 7 días ▼ ]
```

Posibles períodos:

-   7 días.
-   30 días.
-   Este mes.

No agregar filtros que backend todavía no pueda soportar sin implementar
su lógica correspondiente.

------------------------------------------------------------------------

# 16. Productos / categorías más vendidos

Agregar una card:

``` text
Productos más vendidos
```

Ejemplo:

``` text
1. Remera niña             18
2. Body bebé               14
3. Calza biker             11
4. Enterito                 9
```

Opcionalmente mostrar:

-   Cantidad vendida.
-   Importe generado.
-   Categoría.

------------------------------------------------------------------------

# 17. Stock que requiere atención

Agregar una sección de alta utilidad:

``` text
Stock que requiere atención
```

Ejemplo:

``` text
Producto            Stock

Body T2               2    Bajo
Remera T6             1    Bajo
Calza T10             0    Sin stock
```

Estados:

``` text
Normal
Bajo stock
Sin stock
```

Debe existir una acción:

``` text
Ver productos
```

No asumir un umbral de stock arbitrario si el sistema ya posee
configuración para ello.

------------------------------------------------------------------------

# 18. Actividad reciente

Actualmente la card tiene demasiado espacio vacío.

Convertirla en una lista compacta.

Ejemplo:

``` text
Actividad reciente

Venta #152
$18.000 · hace 5 min

Producto actualizado
Remera niña · hace 20 min

Venta #151
$32.500 · hace 35 min
```

Mostrar entre 5 y 10 elementos.

Agregar:

``` text
Ver toda la actividad
```

solo si existe o se implementa una pantalla correspondiente.

------------------------------------------------------------------------

# 19. Layout recomendado

Desktop:

``` text
┌────────┬────────┬────────┬────────┐
│ KPI 1  │ KPI 2  │ KPI 3  │ KPI 4  │
└────────┴────────┴────────┴────────┘

┌─────────────────────────┬───────────────┐
│                         │               │
│ Ventas últimos 7 días   │ Más vendidos │
│                         │               │
│       GRÁFICO           │ Producto  18 │
│                         │ Producto  14 │
│                         │ Producto  11 │
└─────────────────────────┴───────────────┘

┌─────────────────────────┬───────────────┐
│ Stock / Alertas         │ Actividad     │
│                         │ reciente      │
└─────────────────────────┴───────────────┘
```

------------------------------------------------------------------------

# 20. Grid responsive

Utilizar CSS Grid o el sistema de layout existente.

Ejemplo conceptual:

``` css
.dashboard-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
```

Tablet:

``` css
grid-template-columns: repeat(2, minmax(0, 1fr));
```

Mobile:

``` css
grid-template-columns: 1fr;
```

Evitar anchos fijos en las cards.

------------------------------------------------------------------------

# 21. Ancho del contenido

Evitar que el contenido quede excesivamente estirado en monitores
grandes.

Utilizar un container:

``` css
.dashboard-container {
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
}
```

El valor exacto puede adaptarse al diseño existente.

------------------------------------------------------------------------

# 22. Espaciado

Definir una escala consistente.

Ejemplo:

``` text
4px
8px
12px
16px
24px
32px
48px
```

No utilizar valores aleatorios para cada componente.

------------------------------------------------------------------------

# 23. Cards

Todas las cards deben compartir:

``` text
border
border-radius
background
shadow
padding
```

Ejemplo:

``` css
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
```

Evitar sombras pronunciadas.

------------------------------------------------------------------------

# 24. Paleta

Reutilizar los tokens del login.

Ejemplo:

``` css
:root {
    --primary: #16a34a;
    --primary-hover: #15803d;

    --background: #f8fafc;
    --surface: #ffffff;

    --sidebar-background: #0f172a;
    --sidebar-text: #cbd5e1;
    --sidebar-text-active: #ffffff;

    --text-primary: #0f172a;
    --text-secondary: #64748b;

    --border: #e2e8f0;

    --success: #16a34a;
    --warning: #d97706;
    --danger: #dc2626;
}
```

Centralizar estos valores.

------------------------------------------------------------------------

# 25. Tipografía

Mantener la misma tipografía del login.

Preferencia:

``` text
Inter
```

Jerarquía aproximada:

``` text
Dashboard:      32px / 700
Section title:  18px / 600
KPI value:      28px / 700
Body:           14px / 400
Label:          12-13px / 500
Sidebar:        14px / 500
```

------------------------------------------------------------------------

# 26. Estados vacíos

No mostrar cards completamente vacías.

Ejemplo para actividad:

``` text
Todavía no hay actividad reciente.

Las ventas y modificaciones realizadas
aparecerán acá.
```

Ejemplo para gráfico:

``` text
Todavía no hay ventas para este período.
```

Agregar CTA solamente cuando sea útil.

------------------------------------------------------------------------

# 27. Loading

Evitar saltos bruscos mientras cargan los datos.

Utilizar skeletons.

Ejemplo:

``` text
┌──────────────────────┐
│ ███████              │
│ ████████████         │
│ █████                │
└──────────────────────┘
```

No utilizar un spinner gigante en el centro del dashboard.

------------------------------------------------------------------------

# 28. Errores

Si una sección falla, evitar bloquear todo el dashboard.

Ejemplo:

``` text
No pudimos cargar la analítica de ventas.

[ Reintentar ]
```

Cada widget debería poder manejar su propio estado cuando la
arquitectura lo permita.

------------------------------------------------------------------------

# 29. Responsive general

### Desktop

``` text
Sidebar fijo
Header
4 KPIs
Grid de dos columnas
```

### Tablet

``` text
Sidebar drawer o reducido
2 KPIs por fila
Contenido adaptable
```

### Mobile

``` text
Header mobile
Sidebar drawer
1 KPI por fila
Cards al 100%
Gráficos responsive
Acciones adaptadas
```

No debe existir scroll horizontal.

------------------------------------------------------------------------

# 30. Accesibilidad

Implementar:

-   Navegación mediante teclado.
-   `focus-visible`.
-   Botones reales para acciones.
-   Labels accesibles.
-   Contraste suficiente.
-   `aria-expanded` para dropdowns.
-   `aria-expanded` para sidebar mobile.
-   `aria-controls` cuando corresponda.
-   Escape para cerrar overlays.
-   Tooltips accesibles cuando el sidebar esté colapsado.

------------------------------------------------------------------------

# 31. Rendimiento

Evitar cargar librerías pesadas innecesariamente.

Para gráficos utilizar una librería existente en el proyecto cuando sea
posible.

Si no existe ninguna, elegir una solución liviana y mantenible.

No agregar múltiples librerías para resolver el mismo problema.

------------------------------------------------------------------------

# 32. Componentización

Separar conceptualmente:

``` text
AppLayout
Sidebar
SidebarItem
Header
UserMenu
DashboardHeader
KpiCard
QuickActions
SalesChart
TopProducts
LowStock
RecentActivity
EmptyState
LoadingSkeleton
ErrorState
```

Adaptar estos nombres a la arquitectura Laravel existente.

No crear abstracciones innecesarias si Blade ya permite resolverlo
limpiamente.

------------------------------------------------------------------------

# 33. Integración con Laravel

Antes de realizar cambios:

1.  Analizar layouts Blade existentes.
2.  Identificar componentes reutilizables.
3.  Revisar rutas existentes.
4.  Revisar controllers/services que alimentan el dashboard.
5.  Revisar CSS/JS utilizado actualmente.
6.  Reutilizar funcionalidad existente.

No modificar backend únicamente para satisfacer cambios visuales salvo
que sea necesario para las nuevas métricas aprobadas.

------------------------------------------------------------------------

# 34. Mantener separación de responsabilidades

La vista no debe contener lógica compleja.

Preferir:

``` text
Controller / Service
        ↓
Datos preparados
        ↓
Blade / componente
```

Evitar consultas directas desde las vistas.

------------------------------------------------------------------------

# 35. Prioridades de implementación

## Fase 1 --- Layout

Implementar primero:

-   Sidebar 100% del alto.
-   Header.
-   Responsive.
-   Grid.
-   Sistema visual.
-   Cards.
-   Acciones rápidas.

## Fase 2 --- Datos

Implementar:

-   KPIs.
-   Actividad reciente.
-   Stock bajo.
-   Productos más vendidos.

## Fase 3 --- Analítica

Implementar:

-   Gráfico de ventas.
-   Selector de período.
-   Estados loading/error/empty.

De esta forma se evita mezclar una refactorización visual grande con
demasiados cambios funcionales al mismo tiempo.

------------------------------------------------------------------------

# 36. Criterios de aceptación

El rediseño se considera terminado cuando:

-   [ ] El sidebar ocupa el 100% del alto visible en desktop.
-   [ ] El fondo del sidebar continúa hasta el final de la pantalla
    aunque existan pocas opciones.
-   [ ] El footer del usuario permanece en la parte inferior del
    sidebar.
-   [ ] El contenido del sidebar puede hacer scroll independientemente
    si supera la altura disponible.
-   [ ] El contenido principal respeta correctamente el ancho del
    sidebar.
-   [ ] En mobile el sidebar funciona como drawer.
-   [ ] No existe scroll horizontal.
-   [ ] El dashboard funciona correctamente en desktop, tablet y mobile.
-   [ ] Los estados activos del menú son claros.
-   [ ] Existe consistencia visual con el login.
-   [ ] Los KPIs poseen jerarquía visual adecuada.
-   [ ] Las acciones rápidas son responsive.
-   [ ] Las cards utilizan estilos reutilizables.
-   [ ] Existen estados de loading.
-   [ ] Existen estados vacíos.
-   [ ] Existen estados de error.
-   [ ] La actividad reciente no desperdicia espacio vertical
    innecesario.
-   [ ] Los gráficos se adaptan al contenedor.
-   [ ] La navegación mediante teclado funciona.
-   [ ] No se rompe ninguna ruta existente.
-   [ ] No se modifica innecesariamente la lógica de negocio.

------------------------------------------------------------------------

# 37. Instrucción final para IA / SDD

Tomar el dashboard existente de **TiendaStock** como punto de partida y
refactorizar su interfaz siguiendo esta especificación.

La captura actual debe utilizarse como referencia de la funcionalidad y
estructura existente, no como una limitación del nuevo diseño.

Priorizar un resultado:

``` text
Profesional
Minimalista
Responsive
Reutilizable
Accesible
Orientado a gestión comercial
```

## Restricciones

No realizar cambios fuera del alcance del dashboard sin justificación.

No eliminar funcionalidades existentes.

No reemplazar la arquitectura actual innecesariamente.

No introducir dependencias pesadas cuando CSS/JS existente sea
suficiente.

No utilizar datos ficticios como implementación definitiva.

No hardcodear métricas que deban provenir del backend.

## Requisito visual obligatorio

El **sidebar debe ocupar todo el alto del viewport**.

En desktop:

``` css
height: 100dvh;
```

y debe mantenerse visualmente hasta el borde inferior de la pantalla,
independientemente de la altura del contenido principal.

El bloque del usuario debe quedar anclado visualmente al final del
sidebar mediante un layout flex, no mediante posiciones absolutas
innecesarias.

## Validación final

Antes de considerar terminada la tarea comprobar:

1.  Desktop grande.
2.  Notebook.
3.  Tablet.
4.  Mobile.
5.  Sidebar con poco contenido.
6.  Sidebar con contenido que exceda la pantalla.
7.  Apertura/cierre del drawer mobile.
8.  Navegación mediante teclado.
9.  KPIs sin datos.
10. KPIs con datos.
11. Loading.
12. Error.
13. Actividad vacía.
14. Gráficos responsive.
15. Rutas y acciones rápidas existentes.
16. Cierre de sesión.
