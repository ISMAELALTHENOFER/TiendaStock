# SDD — Rediseño Frontend de TiendaStock

## 1. Propósito

Realizar un rediseño visual integral del frontend de **TiendaStock**, manteniendo la funcionalidad existente y mejorando significativamente la experiencia de usuario, la consistencia visual y la percepción profesional de la aplicación.

El objetivo es evolucionar la interfaz actual hacia un estilo **SaaS moderno, minimalista, profesional y responsive**, similar a aplicaciones modernas de gestión administrativa.

El rediseño debe realizarse **sin modificar innecesariamente la lógica de negocio existente**.

La prioridad es mejorar:

- Jerarquía visual.
- Consistencia entre pantallas.
- Navegación.
- Legibilidad.
- Espaciado.
- Componentización.
- Feedback visual.
- Responsive design.
- Experiencia de usuario.

---

## 2. Contexto actual

La aplicación está desarrollada con **Laravel** y cuenta, entre otras, con las siguientes secciones:

- Dashboard.
- Ventas.
- Productos.
- Categorías.
- Usuarios.

El frontend actual posee:

- Sidebar lateral.
- Header superior.
- Cards de estadísticas.
- Tablas.
- Filtros.
- Botones de acciones.
- Badges para estados.
- Acciones rápidas.
- Actividad reciente.

La funcionalidad existente debe conservarse.

El trabajo solicitado es principalmente de **UI/UX y arquitectura visual del frontend**.

---

## 3. Objetivos

### Objetivo principal

Crear una identidad visual coherente para todo TiendaStock.

### Objetivos secundarios

1. Modernizar la interfaz.
2. Mejorar la navegación.
3. Crear componentes reutilizables.
4. Unificar colores, tipografías, bordes, botones y estados.
5. Mejorar el Dashboard.
6. Mejorar la pantalla de Historial de Ventas.
7. Mantener compatibilidad con Laravel.
8. Garantizar responsive design.
9. Reducir elementos visualmente innecesarios.
10. Mantener una interfaz sencilla de entender para usuarios no técnicos.

---

## 4. Dirección visual

La interfaz debe seguir estos principios:

### Estilo

**Modern SaaS / Admin Dashboard**

Características:

- Minimalista.
- Profesional.
- Limpio.
- Buena utilización del espacio.
- Jerarquía visual clara.
- Pocos colores.
- Bordes suaves.
- Sombras muy sutiles.
- Animaciones discretas.
- Componentes consistentes.

Evitar:

- Gradientes excesivos.
- Sombras fuertes.
- Colores saturados.
- Demasiados elementos decorativos.
- Cards innecesariamente grandes.
- Animaciones exageradas.
- Diferentes estilos para componentes equivalentes.

---

## 5. Sistema visual

### 5.1 Tipografía

Utilizar preferentemente:

**Inter**

Como alternativa:

**Plus Jakarta Sans**

Jerarquía aproximada:

```text
Título principal: 28px / 700
Título de sección: 16px / 600
Texto normal: 14px / 400
Texto secundario: 12px / 400
Valores importantes: 24px / 700
```

Los tamaños pueden ajustarse según el contexto y responsive.

---

## 6. Sistema de colores

Utilizar una paleta reducida.

### Primary

Verde.

Debe utilizarse principalmente para:

- Nueva venta.
- Acciones principales.
- Confirmaciones.
- Estados positivos.

### Neutrales

Utilizar:

- Blanco.
- Gris muy claro.
- Gris.
- Gris oscuro.
- Negro/azul muy oscuro.

Para:

- Fondos.
- Cards.
- Tablas.
- Textos.
- Borders.

### Estados

Definir colores semánticos:

```text
Success → verde
Warning → amarillo
Danger  → rojo
Info    → azul
```

Los colores deben ser consistentes en todas las pantallas.

---

## 7. Design System

Antes de modificar cada pantalla, crear o consolidar componentes reutilizables.

Se deben priorizar componentes como:

```text
Layout
Sidebar
Header
Card
Button
Input
Select
Badge
Modal
Table
Dropdown
EmptyState
LoadingState
Toast
```

Los componentes deben permitir reutilización entre:

- Dashboard.
- Ventas.
- Productos.
- Categorías.
- Usuarios.

No duplicar estilos cuando el mismo componente pueda reutilizarse.

---

## 8. Layout general

La aplicación debe utilizar una estructura:

```text
┌───────────────┬─────────────────────────────────────┐
│               │ Header                              │
│    Sidebar    ├─────────────────────────────────────┤
│               │                                     │
│               │ Main Content                        │
│               │                                     │
│               │                                     │
└───────────────┴─────────────────────────────────────┘
```

### Sidebar

Debe contener:

```text
TiendaStock

PRINCIPAL
Dashboard

OPERACIONES
Ventas

INVENTARIO
Productos
Categorías

ADMINISTRACIÓN
Usuarios

────────────────────

Usuario actual
```

Características:

- Fijo en desktop.
- Responsive en mobile.
- Opción de colapsar en desktop si resulta conveniente.
- Estado activo claramente visible.
- Iconografía consistente.
- Separación visual entre grupos.
- No utilizar iconos diferentes para acciones similares.

---

## 9. Header

El header actual tiene demasiado espacio vacío.

Debe incorporar, cuando la funcionalidad exista:

```text
Buscar...              Notificaciones   Usuario
```

Como mínimo:

- Usuario actual.
- Menú de usuario.

Opcional:

- Buscador global.
- Notificaciones.

No agregar funcionalidades que requieran backend o nueva lógica solo por motivos visuales. Si se propone alguna, documentarla como opcional.

---

## 10. Dashboard

El Dashboard debe ser la pantalla más importante visualmente.

### 10.1 Métricas

Mantener las métricas actuales:

- Total Productos.
- Categorías.
- Productos Activos.
- Valor Total.

Rediseñarlas para mejorar jerarquía visual.

Ejemplo conceptual:

```text
┌─────────────────┐
│ TOTAL PRODUCTOS │
│                 │
│ 101             │
└─────────────────┘
```

No inventar métricas si no existen en backend.

---

### 10.2 Acciones rápidas

Mantener:

- Nueva Venta.
- Nuevo Producto.
- Nueva Categoría.
- Nuevo Usuario.

La acción **Nueva Venta** debe tener mayor jerarquía visual.

Las acciones deben ser compactas y no ocupar innecesariamente gran cantidad de espacio.

---

### 10.3 Actividad reciente

Mantener la sección de actividad reciente.

Mejorar:

- Iconografía.
- Espaciado.
- Jerarquía.
- Estados.
- Fecha/hora.
- Legibilidad.

No modificar la lógica que genera las actividades.

---

### 10.4 Gráficos

Evaluar incorporar gráficos al Dashboard únicamente si existen datos suficientes en backend.

Posibles gráficos:

```text
Ventas por período
Ventas por categoría
Productos más vendidos
```

No crear gráficos con datos ficticios.

Si no existen datos necesarios, dejar preparado el componente o documentar la necesidad como tarea futura.

---

## 11. Historial de Ventas

La pantalla de ventas debe recibir una mejora importante de UX.

Mantener:

- Filtros.
- Tabla.
- Estados.
- Nueva Venta.
- Visualización de venta.
- Cancelación.

### Filtros

Mejorar la distribución:

```text
Buscar

Desde        Hasta        Estado

[ fecha ]    [ fecha ]    [ estado ]

                    [Filtrar] [Limpiar]
```

Los filtros deben ser claros y ocupar el menor espacio posible.

---

## 12. Tabla de ventas

Mantener las columnas actuales:

```text
#
Fecha
Items
Total
Entrega
Procesado por
Estado
Acciones
```

Mejorar:

- Espaciado.
- Alineación.
- Tipografía.
- Estados.
- Hover.
- Acciones.
- Responsive.

Los valores monetarios deben mantener formato consistente.

---

## 13. Acciones de tabla

Reducir la cantidad de iconos visibles cuando sea posible.

Evaluar utilizar:

```text
Ver
⋮
```

El menú contextual podría contener:

```text
Ver detalle
────────────
Cancelar venta
```

No mostrar acciones que no correspondan al estado actual de la venta.

Ejemplo:

```text
Completada → Ver + Cancelar
Anulada    → Ver
```

La lógica real de permisos y estados debe mantenerse.

---

## 14. Badges

Unificar todos los estados mediante un componente `Badge`.

Ejemplo:

```text
Completada
Anulada
Pendiente
Procesando
```

Los badges deben:

- Ser compactos.
- Tener bordes redondeados.
- Utilizar colores semánticos.
- Mantener el mismo estilo en toda la aplicación.

---

## 15. Botones

Definir variantes:

```text
Primary
Secondary
Danger
Ghost
Icon
```

Ejemplo:

```text
Primary   → Nueva Venta
Secondary → Limpiar
Danger    → Cancelar
Ghost     → Acciones secundarias
Icon      → Ver / editar
```

Todos deben compartir:

- Altura.
- Border radius.
- Tipografía.
- Estados hover.
- Estado disabled.
- Focus.

---

## 16. Inputs y filtros

Unificar:

- Inputs.
- Selects.
- Datepickers.
- Search inputs.

Todos deben compartir:

```text
altura
border
border-radius
font-size
focus state
disabled state
error state
```

No utilizar diferentes estilos para inputs equivalentes.

---

## 17. Cards

Todos los cards deben utilizar un patrón común.

Características:

```text
border-radius: aproximadamente 12px
border: 1px solid neutral
shadow: muy sutil
background: blanco
```

Evitar sombras fuertes.

Los cards deben utilizar spacing consistente.

---

## 18. Responsive

El diseño debe funcionar correctamente en:

```text
Desktop
Tablet
Mobile
```

En mobile:

- Sidebar → drawer.
- Tablas → scroll horizontal o presentación alternativa.
- Cards → una columna.
- Filtros → múltiples filas.
- Acciones → adaptadas al ancho disponible.
- Header → elementos compactos.

No ocultar información importante simplemente para resolver problemas de responsive.

---

## 19. Animaciones

Agregar únicamente animaciones sutiles:

```text
hover
focus
modal
dropdown
sidebar
loading
```

Duración aproximada:

```text
150ms – 250ms
```

Evitar animaciones innecesarias.

---

## 20. Restricciones técnicas

El rediseño debe:

- Mantener Laravel.
- Mantener la lógica de negocio existente.
- Mantener endpoints existentes salvo que sea estrictamente necesario.
- Mantener las rutas actuales.
- Mantener permisos.
- Mantener validaciones.
- Mantener funcionalidades existentes.
- Evitar cambios innecesarios en backend.
- Evitar duplicación de CSS.
- Priorizar componentes reutilizables.
- Mantener compatibilidad con el stack frontend actual.

Antes de incorporar una nueva dependencia, evaluar si puede resolverse con las herramientas existentes.

---

## 21. Criterios de aceptación

### Visual

- [ ] Todas las pantallas principales tienen una identidad visual consistente.
- [ ] Sidebar y Header comparten un diseño coherente.
- [ ] Tipografía unificada.
- [ ] Colores unificados.
- [ ] Botones unificados.
- [ ] Inputs unificados.
- [ ] Badges unificados.
- [ ] Cards unificados.
- [ ] Espaciado consistente.
- [ ] No existen elementos visualmente desalineados.

### Dashboard

- [ ] Métricas rediseñadas.
- [ ] Acciones rápidas rediseñadas.
- [ ] Actividad reciente rediseñada.
- [ ] Se evalúa incorporación de gráficos sin inventar datos.

### Ventas

- [ ] Filtros rediseñados.
- [ ] Tabla rediseñada.
- [ ] Estados rediseñados.
- [ ] Acciones mejoradas.
- [ ] Se conserva la funcionalidad existente.

### Responsive

- [ ] Desktop correcto.
- [ ] Tablet correcto.
- [ ] Mobile correcto.

### Código

- [ ] Componentes reutilizables.
- [ ] Sin duplicación innecesaria.
- [ ] CSS organizado.
- [ ] No se rompe funcionalidad existente.
- [ ] No se modifican reglas de negocio sin justificación.

---

# 22. Estrategia de implementación SDD

## Fase 1 — Explore / Auditoría

Analizar el frontend actual.

Identificar:

- Layout principal.
- Componentes existentes.
- CSS existente.
- Dependencias.
- Sistema de iconos.
- Framework CSS utilizado.
- Vistas Blade.
- JavaScript existente.
- Flujo de navegación.
- Funcionalidades dependientes del backend.

**No modificar código durante esta fase.**

Generar un resumen de los hallazgos y de las oportunidades de mejora.

---

## Fase 2 — Design

Definir el diseño técnico del nuevo frontend.

Definir:

- Design System.
- Paleta de colores.
- Tipografía.
- Espaciado.
- Border radius.
- Sombras.
- Componentes reutilizables.
- Estructura del Layout.
- Sidebar.
- Header.
- Cards.
- Buttons.
- Inputs.
- Badges.
- Tables.
- Modals.
- Estados de carga y vacío.

El diseño debe basarse en los componentes y dependencias existentes siempre que sea posible.

---

## Fase 3 — Tasks

Dividir la implementación en tareas pequeñas, independientes y verificables.

Orden recomendado:

1. Design tokens.
2. Componentes base.
3. Sidebar.
4. Header.
5. Layout.
6. Dashboard.
7. Ventas.
8. Productos.
9. Categorías.
10. Usuarios.
11. Responsive.
12. Estados y feedback.
13. Limpieza/refactor de estilos.

Cada tarea debe especificar:

- Objetivo.
- Archivos involucrados.
- Cambios esperados.
- Dependencias.
- Criterios de aceptación.
- Validaciones necesarias.

---

## Fase 4 — Implementación

Implementar las tareas respetando el Design y evitando modificaciones fuera del alcance.

Reglas:

- No modificar backend si no es necesario.
- No cambiar lógica de negocio.
- No romper rutas.
- No eliminar funcionalidades existentes.
- Reutilizar componentes.
- Evitar duplicación.
- Mantener compatibilidad con el proyecto actual.

---

## Fase 5 — Verificación

Validar:

- Navegación.
- Formularios.
- Filtros.
- Modales.
- Tablas.
- Estados.
- Permisos.
- Responsive.
- Errores visuales.
- Consola JS.
- Errores Laravel.
- Regresiones funcionales.

---

# 23. Regla importante para la IA

> **No comenzar modificando código directamente. Primero analizar la implementación actual y determinar qué componentes, estilos y layouts existentes pueden reutilizarse.**

> **No modificar lógica de negocio para solucionar problemas exclusivamente visuales.**

> **No inventar información, métricas, ventas, gráficos o funcionalidades que no existan actualmente.**

> **Priorizar reutilización y consistencia sobre soluciones específicas para una sola pantalla.**

> **Si existe un componente equivalente, reutilizarlo antes de crear uno nuevo.**

> **Antes de introducir una nueva librería, verificar si la funcionalidad puede implementarse utilizando las dependencias actuales.**

> **Si durante Explore se detecta que una decisión de diseño requiere modificar backend, detener esa tarea y documentar la necesidad antes de implementarla.**

---

# 24. Prompt inicial para el agente SDD

```text
Quiero implementar un rediseño frontend de TiendaStock siguiendo esta especificación SDD.

Antes de realizar cambios:

1. Analizá la estructura actual del proyecto.
2. Identificá el stack frontend utilizado.
3. Identificá layouts, componentes y estilos existentes.
4. Identificá qué partes pueden reutilizarse.
5. Identificá inconsistencias visuales.
6. Verificá cómo están implementadas actualmente las pantallas:
   - Dashboard
   - Ventas
   - Productos
   - Categorías
   - Usuarios
7. Identificá las dependencias frontend existentes.
8. Identificá si existe un Design System parcial.
9. Identificá los componentes que deberían convertirse en componentes reutilizables.
10. Identificá cualquier dependencia entre frontend y backend que pueda verse afectada.

No modifiques código durante esta etapa de análisis.

El objetivo no es reemplazar la aplicación ni su lógica de negocio, sino modernizar progresivamente el frontend existente.

Una vez finalizado el análisis:

1. Generá el diseño técnico.
2. Proponé la estructura de componentes.
3. Definí el Design System.
4. Dividí la implementación en tareas pequeñas.
5. Indicá archivos afectados por cada tarea.
6. Definí criterios de aceptación.
7. Señalá cualquier riesgo o decisión que requiera validación.

No inventes funcionalidades ni datos que no existan actualmente.

Priorizá:

- reutilización;
- componentes;
- consistencia;
- mantenibilidad;
- responsive;
- UX;
- mínima modificación del backend.

No implementes ninguna tarea que no esté contemplada en la especificación o que no sea necesaria para cumplirla.
```
