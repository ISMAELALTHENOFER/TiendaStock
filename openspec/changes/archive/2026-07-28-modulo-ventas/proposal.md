# Propuesta: Módulo de Ventas (POS)

## Problem Statement

TiendaStock gestiona productos, categorías y usuarios, pero **no existe forma de registrar una venta**. El rol Ventas está definido en el sistema (`User::ROLE_VENTAS`) pero no tiene rutas asignadas — los usuarios con ese rol pueden iniciar sesión y ver un dashboard vacío sin poder hacer nada operativo. El stock de productos (`productos.cantidad`) es un entero estático que nunca se decrementa, no hay historial de transacciones, y no hay registro financiero alguno. La aplicación tiene la infraestructura para ser una herramienta de gestión de tienda, pero le falta el núcleo funcional: **vender**.

## Business Goals

1. Habilitar al rol **Ventas** para realizar su función principal: cobrar productos
2. Registrar cada venta con detalle de ítems, precios y método de pago
3. **Deducir stock automáticamente** al concretar una venta, manteniendo integridad de datos
4. Proveer a **ADMIN** visibilidad completa del historial de ventas
5. Sentar las bases para reportes financieros futuros

## Scope

### In Scope (MVP)
- Alta de venta con carrito de productos (POS)
- Búsqueda de productos para agregar al carrito
- Cálculo automático de subtotal, total y cambio
- Input de pago del cliente con cálculo de cambio
- Deducción de stock en transacción atómica (DB transaction)
- Historial de ventas con filtros (fecha, rango)
- Vista detalle / recibo imprimible
- Control de acceso solo para roles **ADMIN** y **Ventas**
- Anulación de venta con restauración de stock
- Validación de stock insuficiente antes de cobrar

### Out of Scope
- Múltiples métodos de pago por venta (split payment)
- Clientes recurrentes / historial por cliente
- Reporte de cierre diario / corte de caja
- Lector de código de barras
- Hold / recall de venta
- Notificaciones de stock bajo
- Migración a PostgreSQL / MySQL
- Multi-tienda / multi-caja

## User Stories

| ID | Rol | Historia |
|----|-----|----------|
| US-01 | Ventas | Quiero buscar productos rápidamente y agregarlos a un carrito para armar una venta en pocos segundos |
| US-02 | Ventas | Quiero ver el total actualizado automáticamente al agregar/quitar productos para evitar errores de cálculo |
| US-03 | Ventas | Quiero ingresar el monto con que paga el cliente y ver el cambio calculado para agilizar el cobro |
| US-04 | Ventas | Quiero que el sistema me advierta si no hay stock suficiente antes de cobrar |
| US-05 | Ventas | Quiero poder anular una venta y que el stock se restablezca automáticamente |
| US-06 | ADMIN | Quiero ver el historial completo de ventas con filtros para hacer seguimiento |
| US-07 | ADMIN | Quiero ver el detalle de cada venta (ítems, precios, quién la procesó) para auditoría |
| US-08 | ADMIN | Quiero que solo los roles ADMIN y Ventas puedan acceder al módulo de ventas |

## Functional Requirements

### Must Have (MVP)
1. **FR-01**: Pantalla POS con diseño de tres zonas: búsqueda, resultados, carrito
2. **FR-02**: Búsqueda de productos por nombre con sugerencias en tiempo real (AJAX, endpoint existente `/productos/search`)
3. **FR-03**: Agregar productos al carrito con cantidad default = 1
4. **FR-04**: Ajustar cantidad desde el carrito, con límite ≤ stock disponible
5. **FR-05**: Eliminar productos del carrito individualmente
6. **FR-06**: Calcular automáticamente: subtotal (suma ítems), total (subtotal − descuento), cambio (pago_con − total)
7. **FR-07**: Input de `pago_con` (monto recibido) con cálculo automático de cambio
8. **FR-08**: Validar que `pago_con ≥ total` antes de habilitar el botón "Cobrar"
9. **FR-09**: Al confirmar venta: crear registro Venta + VentaItems + deducir stock, todo en una DB transaction
10. **FR-10**: Validar stock suficiente para cada producto antes de deducir; si falta stock, error con nombre del producto
11. **FR-11**: Registrar método de pago en la venta (`efectivo`, `tarjeta`, `transferencia`)
12. **FR-12**: Mostrar mensaje de éxito/error en español tras cada operación
13. **FR-13**: Listar ventas en tabla paginada con columnas: fecha, items, total, usuario, estado
14. **FR-14**: Filtrar ventas por rango de fechas
15. **FR-15**: Ver detalle de venta individual con: datos de cabecera, lista de ítems, totales, cambio
16. **FR-16**: Vista de recibo imprimible (`@media print`) con datos de tienda, fecha, ítems, totales
17. **FR-17**: Anular venta: cambia estado a `anulada` y restaura stock de cada producto
18. **FR-18**: Confirmación antes de anular (modal o confirm nativo)
19. **FR-19**: Proteger todas las rutas del módulo con middleware `role:ADMIN,Ventas`
20. **FR-20**: Ocultar enlaces a Ventas en el sidebar para roles que no sean ADMIN o Ventas

### Should Have
21. **FR-21**: Atajo de teclado Enter para agregar producto seleccionado al carrito
22. **FR-22**: Indicador visual de stock bajo (ej. producto con cantidad < 5 se muestra en amarillo/rojo en resultados)
23. **FR-23**: Botón "Vaciar carrito" con confirmación
24. **FR-24**: Feedback visual de carga durante la operación de cobro (estado "procesando..." en botón)

## Non-Functional Requirements

| ID | Requisito |
|----|-----------|
| NFR-01 | **Integridad**: La deducción de stock DEBE ocurrir dentro de una `DB::transaction()` — si falla cualquier ítem, se revierte TODO |
| NFR-02 | **Consistencia**: El precio unitario en `venta_items` DEBE ser una copia del precio de venta al momento de la transacción, no una referencia viva |
| NFR-03 | **Seguridad**: Solo usuarios autenticados con rol ADMIN o Ventas pueden acceder a rutas de ventas |
| NFR-04 | **Auditoría**: Toda venta DEBE registrar `user_id` (quién la procesó) |
| NFR-05 | **Rendimiento**: La búsqueda de productos NO DEBE exceder 300ms en respuesta |
| NFR-06 | **UX**: El POS DEBE ser utilizable en dispositivos móviles (tablet/phone) con diseño responsivo |
| NFR-07 | **Testabilidad**: Cada operación crítica (crear venta, anular, stock insuficiente) DEBE tener test de feature |
| NFR-08 | **Mantenibilidad**: Las validaciones de venta DEBEN estar en un Form Request dedicado (`StoreVentaRequest`) |

## Technical Approach

### Stack & Arquitectura

- **Frontend POS**: Alpine.js para estado del carrito (client-side). Sin Livewire. Comunicación con servidor solo en búsqueda (AJAX) y submit (POST).
- **Backend**: Laravel controller resource con acciones adicionales (`cancel`).
- **Vistas**: Blade con layout `x-app-layout`, Tailwind CSS 3.
- **Base de datos**: SQLite, migraciones estándar Laravel.

### Flujo de Cobro (Store)

```
POST /ventas → VentaController@store
  ├── 1. Validar request (StoreVentaRequest)
  ├── 2. DB::transaction(function () {
  │     ├── 3. Crear Venta (header)
  │     ├── 4. Loop items:
  │     │     ├── Producto::lockForUpdate()->findOrFail()
  │     │     ├── Validar cantidad ≤ stock
  │     │     ├── Crear VentaItem
  │     │     └── $producto->decrement('cantidad', $cantidad)
  │     └── 5. Retornar venta creada
  ├── })
  ├── 6. Redirect a ventas.show con mensaje éxito
  └── Si error → rollback automático + mensaje error
```

### Cancelación

```
POST /ventas/{venta}/cancel → VentaController@cancel
  ├── DB::transaction(function () {
  │     ├── Verificar estado == 'completada'
  │     ├── Loop items:
  │     │     └── $producto->increment('cantidad', $cantidad)
  │     └── Actualizar estado a 'anulada'
  └── })
```

## Data Model

### Tabla: `ventas`

| Columna | Tipo | Restricciones | Propósito |
|---------|------|---------------|-----------|
| id | bigIncrements | PK | — |
| user_id | unsignedBigInteger | FK → users.id, NOT NULL | Quién procesó |
| cliente_nombre | string(255) | nullable | Nombre opcional del cliente |
| subtotal | decimal(10,2) | NOT NULL, default 0 | Suma de subtotales de items |
| descuento | decimal(10,2) | NOT NULL, default 0 | Descuento aplicado |
| impuesto | decimal(10,2) | NOT NULL, default 0 | Impuesto |
| total | decimal(10,2) | NOT NULL | Monto final a cobrar |
| pago_con | decimal(10,2) | NOT NULL | Monto recibido del cliente |
| cambio | decimal(10,2) | NOT NULL, default 0 | Cambio = pago_con − total |
| metodo_pago | string(50) | NOT NULL | efectivo, tarjeta, transferencia |
| estado | string(20) | NOT NULL, default 'completada' | completada, anulada |
| created_at | timestamp | — | Fecha/hora de venta |
| updated_at | timestamp | — | — |

### Tabla: `venta_items`

| Columna | Tipo | Restricciones | Propósito |
|---------|------|---------------|-----------|
| id | bigIncrements | PK | — |
| venta_id | unsignedBigInteger | FK → ventas.id, ON DELETE CASCADE | Venta padre |
| producto_id | unsignedBigInteger | FK → productos.id | Producto vendido |
| cantidad | integer | NOT NULL, > 0 | Cantidad vendida |
| precio_unitario | decimal(10,2) | NOT NULL | Precio al momento de venta (inmutable) |
| subtotal | decimal(10,2) | NOT NULL | cantidad × precio_unitario |
| created_at | timestamp | — | — |
| updated_at | timestamp | — | — |

### Modelos Eloquent

- `Venta`: `HasMany<VentaItem>`, `BelongsTo<User>`
- `VentaItem`: `BelongsTo<Venta>`, `BelongsTo<Producto>`
- `Producto`: agregar `HasMany<VentaItem>` (relación existente)

## UI/UX Concept

### Pantalla POS (`ventas.pos`)

Diseño de dos columnas responsivo:

```
┌─────────────────────────────────────────────────────────────┐
│  [← Volver]  NUEVA VENTA              Cliente: [________] │
├──────────────────────────┬──────────────────────────────────┤
│  🔍 Buscar producto...   │  CARRITO                        │
│  ┌──────────────────────┐│  ┌────────────────────────────┐  │
│  │ Remera Algodón M     ││  │ Producto     Cant  Precio  │  │
│  │ $15.00  Stock: 20    ││  │───────────────────────────│  │
│  │ [Agregar]            ││  │ Remera M      1    $15.00  │  │
│  ├──────────────────────┤│  │ Jean L         2    $40.00  │  │
│  │ Jean Azul L          ││  │ Pantalón M     1    $25.00  │  │
│  │ $20.00  Stock: 8     ││  │───────────────────────────│  │
│  │ [Agregar]            ││  │ Subtotal:           $80.00  │  │
│  ├──────────────────────┤│  │ Descuento:          $0.00   │  │
│  │ Campera Invierno S   ││  │ Total:              $80.00  │  │
│  │ $45.00  Stock: 3 ⚠️ ││  │                             │  │
│  │ [Agregar]            ││  │ 💰 Pago con: [$100.00    ]  │  │
│  └──────────────────────┘│  │ Cambio:            $20.00   │  │
│                          │  │ Método: [Efectivo ▼]        │  │
│                          │  │                             │  │
│                          │  │ [✓ Cobrar]  [🗑 Vaciar]     │  │
│                          │  └────────────────────────────┘  │
└──────────────────────────┴──────────────────────────────────┘
```

**Responsive**: En mobile (< 768px), las columnas se apilan: búsqueda arriba, carrito abajo.

**Recibo**: Vista `ventas.show` con estilos `@media print` — logo, datos, tabla de ítems, totales, firma "Procesado por: [user]". Botón "Imprimir" que ejecuta `window.print()`.

### Pantalla Historial (`ventas.index`)

Tabla con: fecha, items (#), total, procesado por, estado (Completada/Anulada). Filtro por rango de fechas. Click en fila abre detalle. Botón "Anular" con confirmación para ventas completadas.

## Access Control

### Rutas (`routes/web.php`)

```php
Route::middleware(['auth', 'verified', 'role:ADMIN,Ventas'])->group(function () {
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/pos', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/{venta}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
});
```

### Sidebar

El enlace "Ventas" en el sidebar se renderiza condicionalmente:
```blade
@can('view-ventas')  {{-- o @if(in_array(auth()->user()->role, ['ADMIN', 'Ventas'])) --}}
    <a href="{{ route('ventas.index') }}">Ventas</a>
@endif
```

### Middleware

El `CheckRole` middleware existente soporta múltiples roles separados por coma: `role:ADMIN,Ventas`. No requiere cambios.

## Rollback Plan

| Escenario | Acción |
|-----------|--------|
| Migración falla | `php artisan migrate:rollback` — revierte ambas tablas |
| POS produce error en producción | Descomentar rutas en `web.php`, dejar solo el grupo admin existente |
| Bug crítico en stock | Restaurar desde backup de SQLite. Auditoría: las ventas creadas quedan como registro, el stock se puede reconciliar manualmente |
| Feature incompleto | Las rutas están bajo middleware `role:ADMIN,Ventas` — no afectan a otros roles |
| Cancelación errónea | La cancelación es reversible: si se anuló por error, crear una venta nueva con los mismos items (operación manual admin) |

## Risks & Mitigations

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| SQLite lock concurrente (2 cajeros) | Baja (1 caja) | Medio | Documentar limitación. Una sola terminal POS. Futura migración a MySQL/PostgreSQL |
| Stock negativo por race condition | Baja | Alto | `lockForUpdate()` + validación dentro de transacción. En SQLite la serialización de transacciones protege contra escrituras concurrentes |
| Precio de producto cambia después de venta | Media | Medio | Almacenar `precio_unitario` en `venta_items` como copia del precio al momento de la venta |
| Usuario cierra el POS sin cobrar (pérdida carrito) | Alta | Bajo | El carrito es client-side (Alpine.js). Al recargar la página se pierde — comportamiento esperado y documentado. No hay carrito persistente sin cobrar |
| Anular venta y que stock haya cambiado manualmente | Media | Medio | Al anular, la restauración de stock es un `increment()` simple. Si el stock se modificó manualmente entre la venta y la anulación, el valor post-anulación puede no reflejar el estado original. Documentar como limitación conocida |

## Dependencies

| Orden | Dependencia | Detalle |
|-------|-------------|---------|
| 1 | Migraciones | Crear `ventas` y `venta_items` antes que modelos y controladores |
| 2 | Modelos | `Venta`, `VentaItem` — dependen de migraciones existentes |
| 3 | Factories | `VentaFactory`, `VentaItemFactory` — dependen de modelos |
| 4 | Form Request | `StoreVentaRequest` — lo usa el controlador |
| 5 | Controlador | `VentaController` — depende de modelos, request, validaciones |
| 6 | Vistas Blade | Requieren modelos funcionando y rutas definidas |
| 7 | Tests | Dependen de todo lo anterior (TDD: escribir tests antes o en paralelo con implementación) |
| 8 | Sidebar + Dashboard | Modificaciones UI finales, post-implementación |

## Success Criteria

- [ ] **Cobertura de tests**: Feature tests para crear venta, stock insuficiente, carrito vacío, anulación, acceso no autorizado
- [ ] **Stock íntegro**: Tras N ventas de prueba, `productos.cantidad` es exactamente `stock_inicial − ∑cantidades_vendidas + ∑cantidades_anuladas`
- [ ] **Sin regresiones**: `vendor/bin/phpunit` pasa 100% tests existentes + nuevos
- [ ] **UX POS**: Un usuario Ventas puede completar el flujo completo (buscar → agregar → cobrar → imprimir recibo) sin asistencia
- [ ] **Acceso**: Usuario Control Stock NO puede acceder a ninguna ruta de ventas
- [ ] **Calidad código**: `vendor/bin/pint --test` pasa sin errores (PSR-12)

## Capabilities

### New Capabilities
- `ventas-pos`: Pantalla POS con carrito Alpine.js, búsqueda de productos, cálculo de totales y cambio, submit de venta
- `ventas-history`: Historial de ventas con filtros, detalle de venta y recibo imprimible
- `ventas-cancellation`: Anulación de ventas con restauración automática de stock
- `ventas-access-control`: Protección de rutas para roles ADMIN y Ventas

### Modified Capabilities
- None (no hay specs existentes que modificar)
