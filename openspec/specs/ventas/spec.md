# Spec: Módulo de Ventas (POS)

**Change**: modulo-ventas
**Estado**: Draft
**Versión**: 1.0

---

## 1. Introduction

TiendaStock carece de un módulo de ventas. El rol **Ventas** existe en el sistema pero no tiene ninguna ruta operativa asignada: los usuarios con ese rol pueden iniciar sesión y ver un dashboard vacío sin poder realizar ninguna acción transaccional. El stock de productos (`productos.cantidad`) es un valor estático que nunca se decrementa, no existe historial de transacciones y no hay registro financiero alguno.

Este cambio agrega el **Módulo de Ventas (POS)** — un sistema completo de punto de venta que permite a los roles **ADMIN** y **Ventas** registrar ventas con carrito de productos, deducción automática de stock, cálculo de totales y cambio, historial de ventas, recibos imprimibles y anulación de ventas con restauración de stock.

---

## 2. Spec Glossary

| Término | Definición |
|---------|-----------|
| **POS** | Point of Sale — pantalla de cobro con búsqueda de productos, carrito y confirmación de pago |
| **Carrito** | Estado client-side (Alpine.js) con los productos a cobrar, sus cantidades y subtotales |
| **Venta** | Registro en la tabla `ventas` que representa una transacción completada |
| **VentaItem** | Línea de detalle de una venta: producto, cantidad, precio_unitario y subtotal |
| **Pago con (`pago_con`)** | Monto que entrega el cliente para pagar |
| **Cambio** | `pago_con − total`. Debe ser ≥ 0 |
| **Anulación** | Cambio de estado de una venta de `completada` a `anulada`, con restauración del stock |
| **Stock** | Columna `cantidad` en la tabla `productos` |
| **Rol** | Columna `role` en la tabla `users`: `ADMIN`, `Ventas`, `Control Stock` |

---

## 3. Scenarios

### 3.1 FR-01 — Pantalla POS con diseño de tres zonas

**Descripción**: La pantalla POS DEBE mostrar tres zonas diferenciadas: búsqueda de productos, resultados de búsqueda y carrito de compras.

#### Escenario: Visualización correcta del POS
- **Given** un usuario autenticado con rol `Ventas`
- **When** accede a la ruta `GET /ventas/pos`
- **Then** la respuesta es 200 OK
- **And** la vista DEBE contener un campo de búsqueda de productos
- **And** la vista DEBE contener un área de resultados de búsqueda
- **And** la vista DEBE contener un área de carrito con subtotal, total, campo `pago_con`, cambio calculado y botón "Cobrar"
- **And** la vista DEBE ser responsiva: en viewport < 768px las zonas se apilan verticalmente

#### Escenario: Diseño responsivo en mobile
- **Given** un usuario autenticado con rol `ADMIN`
- **When** accede a `GET /ventas/pos` desde un dispositivo con ancho ≤ 767px
- **Then** las tres zonas (búsqueda, resultados, carrito) DEBEN apilarse en una sola columna
- **And** todos los elementos DEBEN ser funcionales sin scroll horizontal

---

### 3.2 FR-02 — Búsqueda de productos por nombre

**Descripción**: El campo de búsqueda DEBE consultar productos por nombre mediante AJAX al endpoint existente `GET /productos/search` y mostrar sugerencias en tiempo real.

#### Escenario: Búsqueda con resultados
- **Given** productos existentes con nombre "Remera Algodón M", "Jean Azul L" y "Campera Invierno S"
- **When** el usuario escribe "Rem" en el campo de búsqueda
- **Then** se dispara una petición AJAX a `GET /productos/search?q=Rem`
- **And** la respuesta JSON DEBE contener "Remera Algodón M"
- **And** NO DEBE contener "Jean Azul L" ni "Campera Invierno S"

#### Escenario: Búsqueda sin resultados
- **Given** ningún producto contiene la cadena "ZXYZ"
- **When** el usuario escribe "ZXYZ" en el campo de búsqueda
- **Then** la respuesta DEBE ser un arreglo vacío `[]`
- **And** la vista DEBE mostrar un mensaje "No se encontraron productos"

#### Escenario: Búsqueda con query vacío
- **Given** el campo de búsqueda está vacío
- **When** el usuario no ha escrito ningún carácter (o borra todo el texto)
- **Then** NO DEBE dispararse ninguna petición AJAX
- **And** la zona de resultados DEBE permanecer vacía

#### Escenario: Rendimiento de búsqueda
- **Given** una base de datos con 1000 productos
- **When** el usuario ejecuta una búsqueda por nombre
- **Then** la respuesta del servidor NO DEBE exceder 300ms (NFR-05)

---

### 3.3 FR-03 — Agregar productos al carrito

**Descripción**: El usuario DEBE poder agregar un producto desde los resultados de búsqueda al carrito con cantidad default = 1.

#### Escenario: Agregar producto único
- **Given** el carrito está vacío
- **When** el usuario hace clic en "Agregar" sobre "Remera Algodón M"
- **Then** el carrito DEBE contener un ítem: producto "Remera Algodón M", cantidad 1, precio_unitario = `$producto->precio_venta`
- **And** el subtotal del carrito DEBE incrementarse en `precio_venta × 1`

#### Escenario: Agregar producto ya existente en el carrito
- **Given** el carrito contiene "Remera Algodón M" con cantidad 1
- **When** el usuario hace clic en "Agregar" sobre el mismo producto
- **Then** la cantidad del ítem DEBE incrementarse a 2
- **And** NO DEBE crearse un segundo ítem para el mismo producto

#### Escenario: Agregar producto con stock = 0
- **Given** un producto "Agotado X" con `cantidad = 0`
- **When** el usuario intenta agregarlo al carrito
- **Then** el sistema DEBE mostrar el producto en resultados pero deshabilitar el botón "Agregar"
- **And** DEBE mostrar una indicación visual "Sin stock"

---

### 3.4 FR-04 — Ajustar cantidad desde el carrito

**Descripción**: El usuario DEBE poder modificar la cantidad de cada producto en el carrito, con límite ≤ stock disponible.

#### Escenario: Aumentar cantidad dentro del stock
- **Given** el carrito contiene "Remera Algodón M" con cantidad 2 y stock disponible = 10
- **When** el usuario incrementa la cantidad a 5
- **Then** el carrito DEBE reflejar cantidad = 5
- **And** el subtotal DEBE recalcularse como `precio_unitario × 5`

#### Escenario: Aumentar cantidad que excede el stock
- **Given** el carrito contiene "Remera Algodón M" con cantidad 8 y stock disponible = 10
- **When** el usuario intenta incrementar la cantidad a 12
- **Then** la cantidad DEBE limitarse a 10 (el stock disponible)
- **And** DEBE mostrarse una advertencia visual "Stock máximo alcanzado"

#### Escenario: Disminuir cantidad a 0 (remover)
- **Given** el carrito contiene "Remera Algodón M" con cantidad 3
- **When** el usuario disminuye la cantidad a 0
- **Then** el ítem DEBE eliminarse del carrito

#### Escenario: Cantidad negativa
- **Given** el carrito contiene un producto
- **When** el usuario ingresa manualmente una cantidad negativa (ej. -1)
- **Then** el sistema DEBE ignorar el valor o fijarlo a 1 (mínimo permitido en carrito)
- **And** NO DEBE permitir cantidades menores a 1

---

### 3.5 FR-05 — Eliminar productos del carrito individualmente

**Descripción**: El usuario DEBE poder eliminar un producto específico del carrito.

#### Escenario: Eliminar producto del carrito
- **Given** el carrito contiene 3 productos: "Remera M", "Jean L", "Campera S"
- **When** el usuario hace clic en "Eliminar" sobre "Jean L"
- **Then** el carrito DEBE contener solo 2 productos: "Remera M" y "Campera S"
- **And** el subtotal DEBE recalcularse sin el producto eliminado

#### Escenario: Eliminar el único producto del carrito
- **Given** el carrito contiene 1 producto: "Remera M"
- **When** el usuario lo elimina
- **Then** el carrito DEBE quedar vacío
- **And** el subtotal DEBE ser 0.00
- **And** el botón "Cobrar" DEBE permanecer deshabilitado

---

### 3.6 FR-06 — Cálculo automático de subtotal, total y cambio

**Descripción**: El sistema DEBE calcular automáticamente subtotal (suma de subtotales de ítems), total (subtotal − descuento + impuesto) y cambio (pago_con − total).

#### Escenario: Cálculo sin descuento ni impuesto
- **Given** el carrito contiene: Remera M (1 × $15.00) y Jean L (2 × $20.00)
- **When** el usuario visualiza el carrito
- **Then** subtotal DEBE ser $55.00
- **And** total DEBE ser $55.00 (subtotal − descuento, con descuento = 0)
- **And** cambio DEBE ser `pago_con − $55.00`

#### Escenario: Cálculo con descuento
- **Given** el carrito tiene subtotal = $100.00 y descuento = $10.00
- **When** el usuario actualiza el descuento
- **Then** total DEBE ser $90.00
- **And** cambio DEBE recalcularse automáticamente

#### Escenario: Cálculo con impuesto
- **Given** el carrito tiene subtotal = $100.00, descuento = $0.00 e impuesto = $21.00
- **When** el usuario visualiza el carrito
- **Then** total DEBE ser $121.00
- **And** cambio DEBE recalcularse automáticamente

---

### 3.7 FR-07 — Input de pago_con con cálculo automático de cambio

**Descripción**: El campo `pago_con` DEBE permitir ingresar el monto recibido del cliente y calcular automáticamente el cambio.

#### Escenario: Pago con monto superior al total
- **Given** el total de la venta es $80.00
- **When** el usuario ingresa `pago_con = 100`
- **Then** el cambio calculado DEBE ser $20.00
- **And** el botón "Cobrar" DEBE habilitarse

#### Escenario: Pago con monto exacto
- **Given** el total de la venta es $80.00
- **When** el usuario ingresa `pago_con = 80`
- **Then** el cambio calculado DEBE ser $0.00
- **And** el botón "Cobrar" DEBE habilitarse

#### Escenario: Pago con monto inferior al total (ver FR-08)
- **Given** el total de la venta es $80.00
- **When** el usuario ingresa `pago_con = 50`
- **Then** el cambio calculado DEBE mostrar un valor negativo o cero
- **And** el botón "Cobrar" DEBE permanecer deshabilitado
- **And** DEBE mostrarse un indicador visual de error en el campo

#### Escenario: Pago con = 0
- **Given** el carrito contiene productos con total > 0
- **When** el usuario deja `pago_con = 0`
- **Then** el cambio calculado DEBE ser −total
- **And** el botón "Cobrar" DEBE permanecer deshabilitado

---

### 3.8 FR-08 — Validar pago_con ≥ total

**Descripción**: El botón "Cobrar" NO DEBE habilitarse hasta que `pago_con ≥ total`.

#### Escenario: Botón Cobrar habilitado
- **Given** total = $80.00
- **When** el usuario ingresa `pago_con = 100`
- **Then** el botón "Cobrar" DEBE estar habilitado (no disabled)

#### Escenario: Botón Cobrar deshabilitado por pago insuficiente
- **Given** total = $80.00
- **When** el usuario ingresa `pago_con = 79.99`
- **Then** el botón "Cobrar" DEBE estar deshabilitado
- **And** DEBE mostrarse una advertencia: "El monto ingresado es menor al total"

#### Escenario: Botón Cobrar deshabilitado con carrito vacío
- **Given** el carrito está vacío (total = $0.00)
- **When** el usuario visualiza el POS
- **Then** el botón "Cobrar" DEBE estar deshabilitado

#### Escenario: Transición de deshabilitado a habilitado
- **Given** total = $50.00 y `pago_con = 30` (Cobrar deshabilitado)
- **When** el usuario cambia `pago_con` a 50
- **Then** el botón "Cobrar" DEBE habilitarse automáticamente sin recargar la página

#### Escenario: Transición de habilitado a deshabilitado
- **Given** total = $50.00 y `pago_con = 60` (Cobrar habilitado)
- **When** el usuario cambia `pago_con` a 40
- **Then** el botón "Cobrar" DEBE deshabilitarse automáticamente sin recargar la página

---

### 3.9 FR-09 — Transacción atómica: crear Venta + VentaItems + deducir stock

**Descripción**: Al confirmar la venta, el sistema DEBE crear el registro `Venta`, sus `VentaItem` y deducir el stock en una sola transacción de base de datos. Si falla cualquier paso, TODO DEBE revertirse.

#### Escenario: Venta exitosa
- **Given** productos con stock suficiente:
  - Remera Algodón M: stock = 20, precio_venta = $15.00
  - Jean Azul L: stock = 10, precio_venta = $40.00
- **When** el usuario completa una venta con:
  - Remera M × 2
  - Jean L × 1
  - pago_con = $80.00
  - metodo_pago = "efectivo"
- **Then** se DEBE crear un registro en `ventas` con:
  - `user_id` = ID del usuario autenticado
  - `subtotal` = $70.00
  - `descuento` = 0.00
  - `total` = $70.00
  - `pago_con` = $80.00
  - `cambio` = $10.00
  - `metodo_pago` = "efectivo"
  - `estado` = "completada"
- **And** se DEBEN crear 2 registros en `venta_items`:
  - Remera M: cantidad = 2, precio_unitario = $15.00, subtotal = $30.00
  - Jean L: cantidad = 1, precio_unitario = $40.00, subtotal = $40.00
- **And** `productos.cantidad` DEBE ser:
  - Remera Algodón M: 20 − 2 = 18
  - Jean Azul L: 10 − 1 = 9
- **And** el usuario DEBE ser redirigido a `ventas.show` con mensaje de éxito "Venta registrada correctamente"

#### Escenario: Fallo en deducción de stock revierte la transacción
- **Given** productos con stock suficiente:
  - Remera M: stock = 5
  - Jean L: stock = 1
- **When** el usuario intenta vender Remera M × 2 y Jean L × 2
- **And** ocurre un error en el servidor durante la deducción del segundo producto (ej. excepción)
- **Then** la transacción DEBE revertirse completamente (rollback)
- **And** `productos.cantidad` DEBE mantener los valores originales: Remera M = 5, Jean L = 1
- **And** NO DEBE haber registros en `ventas` ni `venta_items`

---

### 3.10 FR-10 — Validación de stock suficiente

**Descripción**: El sistema DEBE validar que `cantidad_solicitada ≤ stock_disponible` para cada producto ANTES de deducir stock. Si algún producto no tiene stock suficiente, DEBE mostrar error con el nombre del producto y NO crear la venta.

#### Escenario: Stock insuficiente en un producto
- **Given** "Remera Algodón M" con stock = 3
- **When** el usuario intenta cobrar con cantidad = 5
- **Then** NO DEBE crearse la venta
- **And** el sistema DEBE mostrar el mensaje de error: "Stock insuficiente para: Remera Algodón M"
- **And** `productos.cantidad` para "Remera Algodón M" DEBE permanecer en 3

#### Escenario: Stock insuficiente en múltiples productos
- **Given** "Remera M" stock = 2 y "Jean L" stock = 1
- **When** el usuario intenta cobrar Remera M × 5 y Jean L × 3
- **Then** NO DEBE crearse la venta
- **And** el mensaje de error DEBE mencionar TODOS los productos con stock insuficiente: "Stock insuficiente para: Remera Algodón M, Jean Azul L"
- **And** ambos stocks DEBEN permanecer sin cambios

#### Escenario: Stock exacto (límite justo)
- **Given** "Remera Algodón M" con stock = 5
- **When** el usuario cobra cantidad = 5
- **Then** la venta DEBE crearse exitosamente
- **And** `productos.cantidad` para "Remera Algodón M" DEBE ser 0
- **And** el producto DEBE seguir existiendo (no se elimina, solo stock = 0)

---

### 3.11 FR-11 — Registrar método de pago

**Descripción**: La venta DEBE registrar el método de pago usado. Los valores permitidos son: `efectivo`, `tarjeta`, `transferencia`.

#### Escenario: Venta con efectivo
- **Given** un carrito con productos por total = $50.00
- **When** el usuario selecciona método "efectivo", ingresa `pago_con = 100` y confirma
- **Then** la venta creada DEBE tener `metodo_pago = "efectivo"`

#### Escenario: Venta con tarjeta
- **Given** un carrito con productos por total = $50.00
- **When** el usuario selecciona método "tarjeta" e ingresa `pago_con = 50`
- **Then** la venta creada DEBE tener `metodo_pago = "tarjeta"`
- **And** `cambio` DEBE ser 0.00

#### Escenario: Venta con transferencia
- **Given** un carrito con productos por total = $50.00
- **When** el usuario selecciona método "transferencia" e ingresa `pago_con = 50`
- **Then** la venta creada DEBE tener `metodo_pago = "transferencia"`
- **And** `cambio` DEBE ser 0.00

#### Escenario: Método de pago inválido
- **Given** un carrito con productos
- **When** el usuario intenta enviar con `metodo_pago = "cripto"`
- **Then** el servidor DEBE rechazar la solicitud con error de validación
- **And** DEBE indicar que el método de pago no es válido

---

### 3.12 FR-12 — Mensajes de éxito/error en español

**Descripción**: Tras cada operación (crear venta, anular, error), el sistema DEBE mostrar un mensaje en español.

#### Escenario: Mensaje de éxito al crear venta
- **Given** una venta válida lista para cobrar
- **When** el usuario confirma la venta exitosamente
- **Then** el sistema DEBE redirigir con un mensaje flash: "Venta registrada correctamente"

#### Escenario: Mensaje de error al fallar venta
- **Given** una venta con stock insuficiente
- **When** el usuario intenta cobrar
- **Then** el sistema DEBE mostrar: "Stock insuficiente para: [nombre producto]"

#### Escenario: Mensaje de éxito al anular venta
- **Given** una venta completada
- **When** el usuario la anula exitosamente
- **Then** el sistema DEBE mostrar: "Venta anulada correctamente"

---

### 3.13 FR-13 — Listar ventas en tabla paginada

**Descripción**: La vista de historial DEBE mostrar una tabla paginada con columnas: fecha, items (#), total, usuario, estado.

#### Escenario: Listado con ventas
- **Given** existen 25 ventas registradas
- **When** el usuario accede a `GET /ventas`
- **Then** la respuesta DEBE ser 200 OK
- **And** la tabla DEBE mostrar 15 ventas por página (paginación por defecto)
- **And** cada fila DEBE contener: fecha, cantidad de ítems, total, nombre del usuario que procesó, estado (Completada / Anulada)
- **And** DEBE haber un enlace a la página 2

#### Escenario: Listado sin ventas
- **Given** no existen ventas registradas
- **When** el usuario accede a `GET /ventas`
- **Then** la tabla DEBE mostrar "No hay ventas registradas"
- **And** NO DEBE haber paginación

---

### 3.14 FR-14 — Filtrar ventas por rango de fechas

**Descripción**: El historial DEBE permitir filtrar ventas por fecha desde/hasta.

#### Escenario: Filtrar por rango con resultados
- **Given** ventas del 01/01/2026, 15/01/2026 y 01/02/2026
- **When** el usuario filtra por fecha desde = 01/01/2026 hasta = 20/01/2026
- **Then** la tabla DEBE mostrar solo las ventas del 01/01/2026 y 15/01/2026
- **And** NO DEBE mostrar la venta del 01/02/2026

#### Escenario: Filtrar por rango sin resultados
- **Given** ventas solo en enero 2026
- **When** el usuario filtra por fecha desde = 01/03/2026 hasta = 31/03/2026
- **Then** la tabla DEBE mostrar "No se encontraron ventas en el rango seleccionado"

#### Escenario: Filtrar con solo fecha desde
- **Given** ventas el 01/01/2026, 15/01/2026 y 01/02/2026
- **When** el usuario filtra con fecha desde = 01/02/2026 y fecha hasta vacía
- **Then** la tabla DEBE mostrar ventas desde el 01/02/2026 en adelante

#### Escenario: Filtrar con solo fecha hasta
- **Given** ventas el 01/01/2026, 15/01/2026 y 01/02/2026
- **When** el usuario filtra con fecha desde vacía y fecha hasta = 10/01/2026
- **Then** la tabla DEBE mostrar ventas hasta el 10/01/2026 inclusive

---

### 3.15 FR-15 — Ver detalle de venta individual

**Descripción**: La vista de detalle DEBE mostrar la cabecera de la venta, lista de ítems, totales y cambio.

#### Escenario: Ver detalle de venta completada
- **Given** una venta completada con ID 1, procesada por "Juan", con 2 ítems y total = $70.00
- **When** el usuario accede a `GET /ventas/1`
- **Then** la respuesta DEBE ser 200 OK
- **And** la vista DEBE mostrar:
  - Fecha y hora de la venta
  - Nombre del usuario que la procesó
  - Cliente (si se ingresó)
  - Lista de ítems: producto, cantidad, precio unitario, subtotal
  - Subtotal, descuento, impuesto, total
  - Pago con, cambio
  - Método de pago
  - Estado (Completada)

#### Escenario: Ver detalle de venta anulada
- **Given** una venta con estado "anulada"
- **When** el usuario accede a su detalle
- **Then** la vista DEBE mostrar el estado como "Anulada"
- **And** DEBE mostrar la fecha de anulación (updated_at)

#### Escenario: Ver detalle de venta inexistente
- **Given** no existe venta con ID 9999
- **When** el usuario accede a `GET /ventas/9999`
- **Then** la respuesta DEBE ser 404

---

### 3.16 FR-16 — Vista de recibo imprimible

**Descripción**: La vista de detalle DEBE incluir estilos `@media print` para impresión. DEBE incluir datos de tienda, fecha, ítems, totales y procesado por.

#### Escenario: Recibo se renderiza correctamente
- **Given** una venta completada
- **When** el usuario accede a `GET /ventas/{id}`
- **Then** la vista DEBE contener un botón "Imprimir" que ejecute `window.print()`
- **And** la vista DEBE incluir estilos `@media print` que:
  - Oculten sidebar, topbar y botones de navegación
  - Muestren: nombre de tienda "TiendaStock", fecha, lista de ítems con precios, subtotal, descuento, total, pago_con, cambio, y "Procesado por: [usuario]"

#### Escenario: Recibo en pantalla (sin imprimir)
- **Given** una venta completada
- **When** el usuario visualiza la vista sin imprimir
- **Then** DEBE verse el contenido completo del recibo dentro del layout normal (con sidebar y topbar)

---

### 3.17 FR-17 — Anular venta con restauración de stock

**Descripción**: El usuario DEBE poder anular una venta completada. Al anular, el estado DEBE cambiar a `anulada` y el stock de cada producto DEBE restaurarse.

#### Escenario: Anulación exitosa
- **Given** una venta completada con:
  - Remera M × 2 (stock actual = 18 tras la venta)
  - Jean L × 1 (stock actual = 9 tras la venta)
- **When** el usuario envía `POST /ventas/{id}/cancel`
- **Then** el estado de la venta DEBE cambiar a "anulada"
- **And** `productos.cantidad` DEBE restaurarse:
  - Remera M: 18 → 20
  - Jean L: 9 → 10
- **And** el sistema DEBE mostrar: "Venta anulada correctamente"
- **And** el usuario DEBE ser redirigido al listado de ventas

#### Escenario: Anulación con cambios de stock intermedios
- **Given** una venta que vendió Remera M × 2 (stock era 20, quedó 18)
- **And** un ADMIN modificó manualmente el stock de Remera M a 25
- **When** el usuario anula la venta
- **Then** el stock DEBE incrementarse en 2: 25 → 27 (no se revierte al valor original)
- **And** la venta DEBE quedar como anulada

#### Escenario: Anulación en transacción atómica
- **Given** una venta completada con 3 ítems
- **When** se ejecuta la anulación
- **And** ocurre un error al restaurar el stock del segundo ítem
- **Then** la transacción DEBE revertirse completamente (rollback)
- **And** el estado DEBE permanecer "completada"
- **And** el stock del primer ítem NO DEBE haberse modificado

---

### 3.18 FR-18 — Confirmación antes de anular

**Descripción**: Antes de anular una venta, el sistema DEBE solicitar confirmación.

#### Escenario: Confirmación aparece y se confirma
- **Given** una venta completada visible en el listado
- **When** el usuario hace clic en "Anular"
- **Then** DEBE aparecer una confirmación: "¿Está seguro de anular esta venta? El stock será restaurado."
- **When** el usuario confirma
- **Then** el sistema DEBE proceder con la anulación

#### Escenario: Confirmación cancelada
- **Given** una venta completada
- **When** el usuario hace clic en "Anular" y luego cancela la confirmación
- **Then** NO DEBE ocurrir ninguna operación
- **And** la venta DEBE permanecer como "completada"
- **And** el stock NO DEBE modificarse

---

### 3.19 FR-19 — Protección de rutas con middleware

**Descripción**: Todas las rutas del módulo de ventas DEBEN estar protegidas por los middleware `auth` y `role:ADMIN,Ventas`.

#### Escenario: Usuario autenticado como ADMIN
- **Given** un usuario autenticado con rol `ADMIN`
- **When** accede a cualquier ruta del módulo de ventas (`GET /ventas`, `GET /ventas/pos`, `POST /ventas`, `GET /ventas/{id}`, `POST /ventas/{id}/cancel`)
- **Then** la respuesta DEBE ser 200 OK (para GET) o 302 redirección (para POST exitoso)

#### Escenario: Usuario autenticado como Ventas
- **Given** un usuario autenticado con rol `Ventas`
- **When** accede a cualquier ruta del módulo de ventas
- **Then** la respuesta DEBE ser 200 OK o 302 redirección (según corresponda)

#### Escenario: Usuario autenticado como Control Stock
- **Given** un usuario autenticado con rol `Control Stock`
- **When** accede a cualquier ruta del módulo de ventas
- **Then** la respuesta DEBE ser 403 Forbidden

#### Escenario: Usuario no autenticado
- **Given** un usuario no autenticado (guest)
- **When** intenta acceder a cualquier ruta del módulo de ventas
- **Then** la respuesta DEBE ser 302 redirección al login (middleware `auth`)

#### Escenario: Usuario con rol inexistente
- **Given** un usuario autenticado con un rol no definido en el sistema (ej. `Cajero`)
- **When** accede a una ruta del módulo de ventas
- **Then** la respuesta DEBE ser 403 Forbidden

#### Escenario: Ruta POST a /ventas sin ser Ventas ni ADMIN
- **Given** un usuario autenticado con rol `Control Stock`
- **When** envía `POST /ventas` con datos válidos
- **Then** la respuesta DEBE ser 403 Forbidden
- **And** NO DEBE crearse ninguna venta
- **And** NO DEBE deducirse stock

---

### 3.20 FR-20 — Ocultar enlaces a Ventas en el sidebar

**Descripción**: El enlace "Ventas" en el sidebar DEBE mostrarse solo para usuarios con rol `ADMIN` o `Ventas`.

#### Escenario: ADMIN ve el enlace Ventas
- **Given** un usuario autenticado con rol `ADMIN`
- **When** visualiza cualquier página con el sidebar
- **Then** DEBE ver el enlace "Ventas" que apunta a `route('ventas.index')`

#### Escenario: Ventas ve el enlace Ventas
- **Given** un usuario autenticado con rol `Ventas`
- **When** visualiza el sidebar
- **Then** DEBE ver el enlace "Ventas"

#### Escenario: Control Stock NO ve el enlace Ventas
- **Given** un usuario autenticado con rol `Control Stock`
- **When** visualiza el sidebar
- **Then** NO DEBE ver el enlace "Ventas"

#### Escenario: Enlace Ventas aparece en desktop y mobile
- **Given** un usuario con rol `ADMIN` o `Ventas`
- **When** visualiza el sidebar en desktop (md+)
- **Then** DEBE ver el enlace "Ventas"
- **When** visualiza el drawer de navegación en mobile
- **Then** DEBE ver el enlace "Ventas"

---

### 3.21 Edge Cases

#### EC-01: Cantidad = 0 en la solicitud
- **Given** un producto en el carrito
- **When** el usuario envía el formulario con `cantidad = 0` para ese ítem
- **Then** el servidor DEBE rechazar con error de validación "La cantidad debe ser mayor a 0"
- **And** NO DEBE crearse la venta
- **And** NO DEBE deducirse stock

#### EC-02: Precio de producto negativo en base de datos
- **Given** un producto con `precio_venta = -5.00` (dato existente anómalo)
- **When** el usuario lo agrega al carrito y completa la venta
- **Then** el sistema DEBE usar el valor almacenado como `precio_unitario` en `venta_items` (−$5.00)
- **And** el cálculo de subtotal DEBE reflejar ese valor
- **And** el sistema NO DEBE lanzar una excepción (el precio negativo es un problema de datos, no del POS)
- **Note**: Este es un caso de datos inconsistentes existentes; la validación de precio positivo corresponde al módulo de productos.

#### EC-03: Producto no existente (ID inválido)
- **Given** un producto con ID = 9999 que no existe
- **When** el usuario envía la venta con `producto_id = 9999`
- **Then** el servidor DEBE retornar 404 o error de validación "Producto no encontrado"
- **And** la transacción DEBE revertirse

#### EC-04: Envío de carrito vacío
- **Given** el carrito está vacío (sin productos)
- **When** el usuario envía `POST /ventas` con `items = []`
- **Then** el servidor DEBE rechazar con error de validación "Debe agregar al menos un producto"
- **And** NO DEBE crearse la venta

#### EC-05: Pago_con menor al total en servidor (bypass client-side)
- **Given** un total de $100.00
- **When** el usuario envía `POST /ventas` con `pago_con = 50` (bypassando la validación client-side)
- **Then** el servidor DEBE rechazar con error de validación "El monto recibido debe ser mayor o igual al total"
- **And** NO DEBE crearse la venta

#### EC-06: Anular venta ya anulada (idempotencia)
- **Given** una venta con estado "anulada"
- **When** el usuario envía `POST /ventas/{id}/cancel`
- **Then** el sistema DEBE retornar error: "La venta ya se encuentra anulada"
- **And** el estado DEBE permanecer "anulada"
- **And** el stock NO DEBE modificarse (idempotente)

#### EC-07: Subtotal y total con decimales
- **Given** productos con precios fraccionarios: $15.50, $20.75, $10.99
- **When** se calcula el subtotal
- **Then** el resultado DEBE tener precisión de 2 decimales (ej. $47.24)
- **And** los cálculos NO DEBEN sufrir errores de redondeo por punto flotante

#### EC-08: Valor total muy alto
- **Given** 1000 productos a $999,999.99 cada uno
- **When** se calcula el total
- **Then** el sistema DEBE manejar el valor sin desbordamiento (decimal(10,2) soporta hasta 99999999.99)

#### EC-09: Borrar producto del carrito vía POST (seguridad)
- **Given** un carrito con productos
- **When** un usuario malicioso intenta enviar `producto_id` que no pertenece al stock actual (producto eliminado después de cargar la página)
- **Then** el servidor DEBE validar que el producto existe antes de procesarlo
- **And** si no existe, DEBE rechazar con error y no crear la venta

#### EC-10: Usuario se autentica como Ventas pero su rol cambia antes de submit
- **Given** un usuario con rol `Ventas` que carga la página POS
- **When** antes de enviar el formulario, un ADMIN le cambia el rol a "Control Stock"
- **And** el usuario envía `POST /ventas`
- **Then** el middleware `role:ADMIN,Ventas` DEBE ejecutarse en el servidor
- **And** DEBE retornar 403 Forbidden (el middleware se evalúa en cada request, no confía en el estado client-side)

---

## 4. Non-Functional Specification

| ID | Requisito | Criterio de medición |
|----|-----------|---------------------|
| NFR-01 | **Integridad transaccional**: La creación de venta (insert Venta + VentaItems + decrement stock) DEBE ocurrir dentro de una `DB::transaction()`. Si falla cualquier operación, TODO DEBE revertirse. | Prueba de feature que inyecta una excepción después del primer `decrement()` y verifica que ningún `productos.cantidad` se haya modificado y que no existan registros en `ventas` ni `venta_items`. |
| NFR-02 | **Consistencia de precio histórico**: El `precio_unitario` en `venta_items` DEBE ser una copia del valor de `productos.precio_venta` al momento de la transacción, no una referencia viva. Si el precio del producto cambia después, los registros de venta NO DEBEN verse afectados. | Test que crea una venta, luego modifica `precio_venta` del producto, y verifica que `venta_items.precio_unitario` conserve el valor original. |
| NFR-03 | **Seguridad de acceso**: Solo usuarios autenticados con rol `ADMIN` o `Ventas` pueden acceder a rutas de ventas. | Tests de feature para cada rol y para usuarios no autenticados (ver FR-19). |
| NFR-04 | **Auditoría**: Toda venta DEBE registrar `user_id` (quién la procesó). | Test que crea una venta y verifica que `ventas.user_id` coincida con el ID del usuario autenticado. |
| NFR-05 | **Rendimiento de búsqueda**: La respuesta del endpoint `GET /productos/search` NO DEBE exceder 300ms para cualquier consulta, incluyendo términos que matcheen muchos productos. | Prueba de benchmark con 1000 productos en base de datos y 5 consultas diferentes. El tiempo promedio NO DEBE superar 300ms. |
| NFR-06 | **UX responsiva**: El POS DEBE ser utilizable en dispositivos móviles (tablet/phone) con diseño responsivo. | En viewport ≤ 767px: las columnas se apilan, todos los botones y campos son funcionales, no hay desbordamiento horizontal. |
| NFR-07 | **Testabilidad**: Toda operación crítica (crear venta, stock insuficiente, carrito vacío, anular venta, acceso no autorizado) DEBE tener un test de feature que verifique el resultado esperado. | El archivo de tests DEBE contener al menos un test por cada escenario listado en esta spec. |
| NFR-08 | **Mantenibilidad**: Las validaciones de creación de venta DEBEN estar encapsuladas en un Form Request `StoreVentaRequest`. | El método `VentaController@store` NO DEBE contener lógica de validación inline; DEBE type-hint `StoreVentaRequest`. |
| NFR-09 | **Idempotencia de anulación**: Anular una venta ya anulada NO DEBE modificar el estado ni el stock (debe ser una operación segura de repetir). | Test que llama a `cancel` dos veces sobre la misma venta y verifica que el stock solo se restaure la primera vez. |
| NFR-10 | **Consistencia de paginación**: El listado de ventas DEBE paginarse con 15 registros por página por defecto. | Test que crea 20 ventas y verifica que `GET /ventas` retorne 15 en página 1 y 5 en página 2. |

---

## 5. Acceptance Criteria

- [ ] **CR-01**: Un usuario con rol **Ventas** puede completar el flujo POS completo: buscar producto → agregar al carrito → ajustar cantidad → ingresar pago → cobrar → ver recibo → imprimir
- [ ] **CR-02**: Un usuario con rol **ADMIN** puede acceder al POS, historial, detalle y anular ventas
- [ ] **CR-03**: Un usuario con rol **Control Stock** NO puede acceder a ninguna ruta de ventas (recibe 403)
- [ ] **CR-04**: Un usuario no autenticado es redirigido al login al intentar acceder a rutas de ventas
- [ ] **CR-05**: Al cobrar una venta, el stock se deduce correctamente para cada producto
- [ ] **CR-06**: Si un producto no tiene stock suficiente, la venta se rechaza con mensaje claro y no se modifica ningún stock
- [ ] **CR-07**: Si el carrito está vacío, el botón "Cobrar" está deshabilitado y el servidor rechaza el envío
- [ ] **CR-08**: Si `pago_con < total`, el botón "Cobrar" está deshabilitado y el servidor rechaza el envío
- [ ] **CR-09**: Al anular una venta completada, el stock se restaura correctamente
- [ ] **CR-10**: Anular una venta ya anulada no tiene efecto (idempotente)
- [ ] **CR-11**: La creación de venta y la anulación ocurren dentro de una transacción atómica; si algo falla, todo se revierte
- [ ] **CR-12**: El precio unitario en `venta_items` es una copia inmutable del precio al momento de la venta
- [ ] **CR-13**: El listado de ventas muestra paginación (15 por página) y filtro por rango de fechas
- [ ] **CR-14**: La vista de detalle/recibo incluye estilos `@media print` que ocultan la navegación
- [ ] **CR-15**: El enlace "Ventas" en el sidebar solo es visible para roles ADMIN y Ventas
- [ ] **CR-16**: `vendor/bin/phpunit` pasa el 100% de los tests existentes + nuevos
- [ ] **CR-17**: `vendor/bin/pint --test` pasa sin errores (PSR-12)
- [ ] **CR-18**: Todas las validaciones están en `StoreVentaRequest` (no hay validación inline en el controlador)

---

## 6. Data Dictionary

### Tabla: `ventas`

| Columna | Tipo | Constraints | Valor por defecto | Propósito |
|---------|------|------------|-------------------|-----------|
| id | bigIncrements | PK | — | Identificador único |
| user_id | unsignedBigInteger | FK → users.id, NOT NULL | — | Usuario que procesó la venta |
| cliente_nombre | string(255) | nullable | NULL | Nombre opcional del cliente para el recibo |
| subtotal | decimal(10,2) | NOT NULL | 0.00 | Suma de subtotales de ítems (cant × precio_unitario) |
| descuento | decimal(10,2) | NOT NULL | 0.00 | Descuento aplicado a la venta completa |
| impuesto | decimal(10,2) | NOT NULL | 0.00 | Impuesto aplicado |
| total | decimal(10,2) | NOT NULL | — | Monto final: subtotal − descuento + impuesto |
| pago_con | decimal(10,2) | NOT NULL | — | Monto recibido del cliente |
| cambio | decimal(10,2) | NOT NULL | 0.00 | Cambio: pago_con − total |
| metodo_pago | string(50) | NOT NULL | — | Método de pago: efectivo, tarjeta, transferencia |
| estado | string(20) | NOT NULL | 'completada' | Estado: completada, anulada |
| created_at | timestamp | nullable | NULL | Fecha/hora de creación |
| updated_at | timestamp | nullable | NULL | Fecha/hora de última modificación |

### Tabla: `venta_items`

| Columna | Tipo | Constraints | Valor por defecto | Propósito |
|---------|------|------------|-------------------|-----------|
| id | bigIncrements | PK | — | Identificador único |
| venta_id | unsignedBigInteger | FK → ventas.id, ON DELETE CASCADE, NOT NULL | — | Venta padre |
| producto_id | unsignedBigInteger | FK → productos.id, NOT NULL | — | Producto vendido |
| cantidad | integer | NOT NULL, CHECK (> 0) | — | Cantidad vendida |
| precio_unitario | decimal(10,2) | NOT NULL | — | Precio de venta al momento de la transacción (inmutable) |
| subtotal | decimal(10,2) | NOT NULL | — | cantidad × precio_unitario |
| created_at | timestamp | nullable | NULL | Fecha/hora de creación |
| updated_at | timestamp | nullable | NULL | Fecha/hora de última modificación |

---

## 7. Routes Specification

| Método | URI | Controller#Action | Name | Middleware |
|--------|-----|-------------------|------|-----------|
| GET | `/ventas` | VentaController@index | ventas.index | auth, verified, role:ADMIN,Ventas |
| GET | `/ventas/pos` | VentaController@create | ventas.create | auth, verified, role:ADMIN,Ventas |
| POST | `/ventas` | VentaController@store | ventas.store | auth, verified, role:ADMIN,Ventas |
| GET | `/ventas/{venta}` | VentaController@show | ventas.show | auth, verified, role:ADMIN,Ventas |
| POST | `/ventas/{venta}/cancel` | VentaController@cancel | ventas.cancel | auth, verified, role:ADMIN,Ventas |

---

## 8. Validation Rules (StoreVentaRequest)

| Campo | Regla | Mensaje de error |
|-------|-------|-----------------|
| items | required, array, min:1 | Debe agregar al menos un producto |
| items.*.producto_id | required, integer, exists:productos,id | Producto no encontrado |
| items.*.cantidad | required, integer, min:1 | La cantidad debe ser mayor a 0 |
| metodo_pago | required, string, in:efectivo,tarjeta,transferencia | El método de pago no es válido |
| pago_con | required, numeric, min:0, gte:total (validación post-request) | El monto recibido debe ser mayor o igual al total |
| cliente_nombre | sometimes, string, max:255 | El nombre del cliente no puede exceder 255 caracteres |
| descuento | sometimes, numeric, min:0 | El descuento no puede ser negativo |
| impuesto | sometimes, numeric, min:0 | El impuesto no puede ser negativo |

---

## 9. Change Dependencies

| Orden | Dependencia | Detalle |
|-------|-------------|---------|
| 1 | Migraciones | Crear `ventas` y `venta_items` ANTES que modelos y controladores |
| 2 | Modelos | `Venta`, `VentaItem`, relación `HasMany` en `Producto` |
| 3 | Factories | `VentaFactory`, `VentaItemFactory` para tests |
| 4 | Form Request | `StoreVentaRequest` con reglas de validación |
| 5 | Controlador | `VentaController` con métodos: index, create, store, show, cancel |
| 6 | Vistas Blade | `ventas/index.blade.php`, `ventas/pos.blade.php`, `ventas/show.blade.php` |
| 7 | Sidebar | Modificar `sidebar.blade.php` para agregar enlace condicional "Ventas" |
| 8 | Routes | Agregar grupo de rutas con middleware `role:ADMIN,Ventas` en `web.php` |
| 9 | Tests | Feature tests para todos los escenarios; unit tests para modelos |

---

## 10. Frontend Migration Requirements

### Requirement: Sales routes retain Laravel contracts while using React presentation

The React sales slices MUST preserve the existing `ventas.index`, `ventas.create`/POS, `ventas.store`, `ventas.show`, and `ventas.cancel` routes, `auth`, `verified`, and `role:ADMIN,Ventas` middleware, validation, redirects, Spanish flash messages, atomic stock behavior, cancellation/restoration, and printable receipt behavior. GET filters `desde`, `hasta`, and `estado`, `/productos/search`, cart validation, and all current form fields MUST remain compatible. (Previously: Sales behavior was specified for Blade/Alpine views and must now be presented by React without changing its Laravel contract.)

#### Scenario: Authorized history and filters
- GIVEN an authenticated ADMIN or Ventas user
- WHEN the user opens history and submits existing date/status filters
- THEN the same route and parameters produce the same filtered results and pagination semantics

#### Scenario: POS transaction remains authoritative on Laravel
- GIVEN a permitted user submits a valid React POS form with CSRF
- WHEN Laravel processes it
- THEN existing validation, transaction, stock deduction, redirect, and success flash behavior occur

#### Scenario: Cancellation and print remain available
- GIVEN a completed sale visible to an authorized user
- WHEN the user confirms cancellation or chooses print
- THEN Laravel cancellation/restoration and the existing printable detail with `no-print` navigation remain functional

#### Scenario: Unauthorized or invalid request
- GIVEN a guest, disallowed role, missing CSRF, invalid form, insufficient stock, or insufficient payment
- WHEN the request is submitted
- THEN Laravel retains its current redirect/403/validation/error behavior and no unauthorized mutation occurs

### Requirement: Responsive sales workflows preserve complete information

The React history, POS, and detail screens MUST be mobile-first. At 320px and 767px, POS zones MUST stack, filters MUST wrap, and tables MUST use intentional scrolling or complete mobile cards. At 768px, 1023px, 1024px, and 1440+ the layout MUST remain usable without clipping. No action, status, monetary value, or required field MAY be hidden solely for viewport width.

#### Scenario: Mobile POS
- GIVEN a permitted user at 320px or 767px
- WHEN the POS is used
- THEN search, results, cart, payment, validation, and submit remain reachable without unintended horizontal page scrolling

### Requirement: Sales migration rollback

Each migrated sales route MUST be switchable back to its prior Blade/Alpine entrypoint independently, and global `FRONTEND_DRIVER=blade` MUST restore all legacy surfaces. Rollback MUST preserve sales, inventory, route names, and activity records.

#### Scenario: Route rollback
- GIVEN a React sales slice has a rollout failure
- WHEN its route override is changed to Blade
- THEN the prior Blade/Alpine surface serves the same route and persisted records remain unchanged

## Frontend Migration Non-Goals

No new sales rules, payment methods, permissions, filters, notifications, or replacement of Laravel mutations is included.
