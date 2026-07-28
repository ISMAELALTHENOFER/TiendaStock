# Tasks — Módulo de Ventas (POS)

**Change**: modulo-ventas
**Strict TDD**: tests MUST be written before (or alongside) implementation for every task.

---

## Phase 0: Database

### T0.1 — Create migration for `ventas` table

**Description**: Create a new migration file `create_ventas_table.php` that defines the `ventas` schema: `id` (PK), `user_id` (FK → users.id), `cliente_nombre` (nullable), `subtotal`, `descuento`, `impuesto`, `total`, `pago_con`, `cambio` (all decimal(10,2)), `metodo_pago` (string 50), `estado` (string 20, default 'completada'), and timestamps. Include `foreignId()->constrained()->onDelete('cascade')` for the user FK.

**Files to create**:
- `src/database/migrations/2026_07_28_000010_create_ventas_table.php`

**Dependencies**: None

**Acceptance criteria**:
- [x] Migration file exists with the correct schema matching the data model in the design
- [x] `php artisan migrate` creates the `ventas` table successfully (verified via test)
- [x] `php artisan migrate:rollback` drops the table successfully
- [x] FK to `users.id` with `ON DELETE CASCADE` is defined
- [x] All decimal fields use precision (10, 2)
- [x] `estado` has default value `'completada'`
- [x] `cliente_nombre` is nullable

**Test command**: `vendor/bin/phpunit` (after running migrations in test DB)

---

### T0.2 — Create migration for `venta_items` table

**Description**: Create a new migration file `create_venta_items_table.php` that defines the `venta_items` schema: `id` (PK), `venta_id` (FK → ventas.id, ON DELETE CASCADE), `producto_id` (FK → productos.id, ON DELETE CASCADE), `cantidad` (integer, NOT NULL), `precio_unitario` (decimal(10,2)), `subtotal` (decimal(10,2)), and timestamps.

**Files to create**:
- `src/database/migrations/2026_07_28_000011_create_venta_items_table.php`

**Dependencies**: T0.1 (ventas table must exist first for FK reference)

**Acceptance criteria**:
- [x] Migration file exists with the correct schema
- [x] `php artisan migrate` creates the `venta_items` table successfully (verified via test)
- [x] `php artisan migrate:rollback` reverts both tables
- [x] FK to `ventas.id` with `ON DELETE CASCADE` is defined
- [x] FK to `productos.id` with `ON DELETE CASCADE` is defined
- [x] `cantidad` is integer (not decimal)
- [x] `precio_unitario` and `subtotal` use decimal(10, 2)

**Test command**: `vendor/bin/phpunit`

---

## Phase 1: Models

### T1.1 — Create Venta model

**Description**: Create `App\Models\Venta` with `$table = 'ventas'`, explicit `$fillable` array (all columns: user_id, cliente_nombre, subtotal, descuento, impuesto, total, pago_con, cambio, metodo_pago, estado), `casts()` method returning decimal:2 for all monetary fields and datetime for `created_at`. Add relationships: `user()` (BelongsTo User), `items()` (HasMany VentaItem via venta_id). Add helper methods `isCompletada(): bool` and `isAnulada(): bool`.

**Files to create**:
- `src/app/Models/Venta.php`

**Dependencies**: T0.1 (ventas table exists)

**Acceptance criteria**:
- [x] Model file exists in `App\Models` namespace
- [x] `$fillable` includes all columns from the ventas table
- [x] `casts()` returns decimal:2 for subtotal, descuento, impuesto, total, pago_con, cambio
- [x] `casts()` returns datetime for created_at
- [x] `user()` returns `BelongsTo<User>` relationship
- [x] `items()` returns `HasMany<VentaItem>` relationship with correct foreign key
- [x] `isCompletada()` returns `true` when `estado === 'completada'`
- [x] `isAnulada()` returns `true` when `estado === 'anulada'`
- [x] PSR-12 passes: `vendor/bin/pint --test`

**Test command**: `vendor/bin/phpunit`

---

### T1.2 — Create VentaItem model

**Description**: Create `App\Models\VentaItem` with `$table = 'venta_items'`, explicit `$fillable` (venta_id, producto_id, cantidad, precio_unitario, subtotal), `casts()` returning decimal:2 for both monetary fields. Add relationships: `venta()` (BelongsTo Venta), `producto()` (BelongsTo Producto).

**Files to create**:
- `src/app/Models/VentaItem.php`

**Dependencies**: T0.2 (venta_items table exists)

**Acceptance criteria**:
- [x] Model file exists in `App\Models` namespace
- [x] `$fillable` includes all columns from venta_items table
- [x] `casts()` returns decimal:2 for precio_unitario and subtotal
- [x] `venta()` returns `BelongsTo<Venta>` relationship
- [x] `producto()` returns `BelongsTo<Producto>` relationship
- [x] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

### T1.3 — Add `ventaItems()` relationship to Producto model

**Description**: Add `use Illuminate\Database\Eloquent\Relations\HasMany;` import and a new `ventaItems(): HasMany` method to the existing `Producto` model. The relationship returns `$this->hasMany(VentaItem::class)`.

**Files to modify**:
- `src/app/Models/Producto.php`

**Dependencies**: T1.2 (VentaItem model must exist)

**Acceptance criteria**:
- [x] `HasMany` import added to Producto.php
- [x] `ventaItems()` method exists returning the correct HasMany relationship
- [x] Existing model behavior is unchanged (categoria relationship, ganancia attribute still work)
- [x] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

## Phase 2: Factories

### T2.1 — Create VentaFactory

**Description**: Create `Database\Factories\VentaFactory` with `$model = Venta::class`. The `definition()` method should return sensible defaults: `user_id` => `User::factory()`, `subtotal` => 100.00, `descuento` => 0, `impuesto` => 0, `total` => 100.00, `pago_con` => 100.00, `cambio` => 0, `metodo_pago` => 'efectivo', `estado` => 'completada'. Add a state method `anulada()` that sets `estado` => 'anulada'.

**Files to create**:
- `src/database/factories/VentaFactory.php`

**Dependencies**: T1.1 (Venta model exists)

**Acceptance criteria**:
- [x] Factory file exists with `$model = Venta::class`
- [x] `definition()` returns all required fields with valid defaults
- [x] `anulada()` state method exists
- [x] `Venta::factory()->create()` works in test context (with RefreshDatabase)
- [x] `Venta::factory()->anulada()->create()` creates a venta with `estado = 'anulada'`

**Test command**: `vendor/bin/phpunit`

---

### T2.2 — Create VentaItemFactory

**Description**: Create `Database\Factories\VentaItemFactory` with `$model = VentaItem::class`. The `definition()` method: `venta_id` => `Venta::factory()`, `producto_id` => `Producto::factory()`, `cantidad` => 1, `precio_unitario` => 100.00, `subtotal` => 100.00.

**Note**: `ProductoFactory` does not exist yet. Create it as part of this task (`database/factories/ProductoFactory.php`) with basic product fields, OR use `Producto::create([...])` directly in tests. The factory approach is preferred for consistency.

**Files to create**:
- `src/database/factories/VentaItemFactory.php`
- `src/database/factories/ProductoFactory.php` (new, needed for VentaItemFactory)

**Dependencies**: T2.1 (VentaFactory exists), T1.2 (VentaItem model exists)

**Acceptance criteria**:
- [x] VentaItemFactory exists with correct `$model`
- [x] `definition()` returns all required fields
- [x] ProductoFactory exists (if created) with basic product fields (nombre, precio_venta, cantidad, activo)
- [x] `VentaItem::factory()->create()` works in test context

**Test command**: `vendor/bin/phpunit`

---

## Phase 3: Validation

### T3.1 — Create StoreVentaRequest

**Description**: Create `App\Http\Requests\StoreVentaRequest` extending `FormRequest`. Define:
- `authorize()`: return `true` (auth handled by middleware)
- `rules()`: validate `items` (required, array, min:1), `items.*.producto_id` (required, integer, exists:productos,id), `items.*.cantidad` (required, integer, min:1), `cliente_nombre` (nullable, string, max:255), `subtotal` (required, numeric, min:0), `descuento` (nullable, numeric, min:0), `impuesto` (nullable, numeric, min:0), `total` (required, numeric, min:0), `pago_con` (required, numeric, min:0), `metodo_pago` (required, string, in:efectivo,tarjeta,transferencia)
- `messages()`: all error messages in Spanish (see design §4.2)
- `withValidator()`: two after-validation callbacks — (1) validate `pago_con >= total`, (2) validate stock suficiente for each item against `productos.cantidad`

**Files to create**:
- `src/app/Http/Requests/StoreVentaRequest.php`

**Dependencies**: T1.1 (Venta model), T1.2 (VentaItem model)

**Acceptance criteria**:
- [x] File exists with all rules defined
- [x] All error messages are in Spanish
- [x] `withValidator()` rejects when `pago_con < total` with error message: "El monto recibido debe ser mayor o igual al total de la venta."
- [x] `withValidator()` rejects when any item `cantidad > producto.cantidad` with error mentioning the product name
- [x] `authorize()` returns true
- [x] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

## Phase 4: Controller

### T4.1 — Create VentaController@index — sales history with date filter

**Description**: Create `App\Http\Controllers\VentaController` with the `index(Request $request)` method. Query `Venta::with('user', 'items')->orderBy('created_at', 'desc')`. Apply optional filters: `desde` (date >=), `hasta` (date <=), `estado` (exact match). Paginate with 15 results per page. Return `view('ventas.index', compact('ventas'))`.

Write the test FIRST (before implementing): `test_ventas_page_loads`, `test_filters_by_date_range`, `test_filter_by_estado`, `test_pagination`.

**Files to create**:
- `src/app/Http/Controllers/VentaController.php` (partial — only index method)

**Dependencies**: T1.1 (Venta model), T3.1 (StoreVentaRequest — for future methods)

**Acceptance criteria**:
- [ ] Controller file exists with `index()` method
- [ ] Query uses eager loading (`with('user', 'items')`)
- [ ] Optional date filters (`desde`/`hasta`) work correctly
- [ ] Optional `estado` filter works correctly
- [ ] Results are ordered by `created_at DESC`
- [ ] Results are paginated (15 per page)
- [ ] Test exists and passes: authorized user sees 200 on GET /ventas
- [ ] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

### T4.2 — Create VentaController@create — POS screen

**Description**: Add `create()` method to VentaController that returns `view('ventas.pos')`. No DB queries needed — products are loaded via AJAX from the existing `ProductoController@search` endpoint.

**Files to modify**:
- `src/app/Http/Controllers/VentaController.php` (add create method)

**Dependencies**: T4.1 (controller file exists)

**Acceptance criteria**:
- [ ] `create()` method exists and returns `view('ventas.pos')`
- [ ] Test: `GET /ventas/pos` returns 200 for ADMIN and Ventas roles
- [ ] Test: `GET /ventas/pos` returns 403 for Control Stock

**Test command**: `vendor/bin/phpunit`

---

### T4.3 — Create VentaController@store — sale creation with transactional stock deduction

**Description**: Add `store(StoreVentaRequest $request)` method. Inside a `DB::transaction()`:
1. Create Venta record with `user_id = auth()->id()`, values from validated request, `cambio = pago_con - total`
2. Loop through `items`: `Producto::lockForUpdate()->findOrFail()`, validate `cantidad <= producto.cantidad` (throws RuntimeException if insufficient), create VentaItem with `precio_unitario` copied from `producto.precio_venta`, `subtotal = cantidad * precio_unitario`, then `$producto->decrement('cantidad', $cantidad)`
3. Catch RuntimeException inside store: redirect back with flash error
4. On success: redirect to `ventas.show` with flash success "Venta #N registrada correctamente."

**Files to modify**:
- `src/app/Http/Controllers/VentaController.php` (add store method)

**Dependencies**: T4.2 (controller file exists), T3.1 (StoreVentaRequest exists)

**Acceptance criteria**:
- [ ] `store()` type-hints `StoreVentaRequest`
- [ ] Uses `DB::transaction()` for atomicity
- [ ] Uses `lockForUpdate()` on Producto queries
- [ ] Copies `precio_unitario` from product at transaction time (immutable)
- [ ] Throws RuntimeException with product name on insufficient stock
- [ ] Catches RuntimeException and redirects back with Spanish flash error
- [ ] On success redirects to `ventas.show` with Spanish flash message
- [ ] Transaction rollback verified: if any item fails, no Venta or VentaItem is persisted
- [ ] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

### T4.4 — Create VentaController@show — receipt/detail view

**Description**: Add `show(Venta $venta)` method that eager-loads `$venta->load('user', 'items.producto')` and returns `view('ventas.show', compact('venta'))`. Uses implicit route model binding.

**Files to modify**:
- `src/app/Http/Controllers/VentaController.php` (add show method)

**Dependencies**: T4.3 (controller file exists with full structure)

**Acceptance criteria**:
- [ ] `show()` uses implicit binding (Venta $venta)
- [ ] Eager loads `user` and `items.producto` relationships
- [ ] Returns view `ventas.show`
- [ ] Test: `GET /ventas/{id}` returns 200 for valid venta
- [ ] Test: `GET /ventas/9999` returns 404 for non-existent venta

**Test command**: `vendor/bin/phpunit`

---

### T4.5 — Create VentaController@cancel — sale cancellation with stock restoration

**Description**: Add `cancel(Venta $venta)` method. First check if already anulada (return error flash). Inside `DB::transaction()`: loop through `$venta->items`, for each item call `Producto::lockForUpdate()->increment('cantidad', $item->cantidad)`. Then `$venta->update(['estado' => 'anulada'])`. Redirect to `ventas.index` with success flash "Venta #N anulada. Stock restaurado."

**Files to modify**:
- `src/app/Http/Controllers/VentaController.php` (add cancel method)

**Dependencies**: T4.4 (controller fully built)

**Acceptance criteria**:
- [ ] `cancel()` checks `$venta->isAnulada()` before processing
- [ ] Returns error flash without modifying DB if already anulada
- [ ] Uses `DB::transaction()` with `lockForUpdate` on each producto
- [ ] Restores stock via `increment('cantidad', ...)` for each item
- [ ] Sets `estado = 'anulada'` after restoring stock
- [ ] Redirects to `ventas.index` with Spanish flash success
- [ ] Test: cancel completes successfully and stock is restored
- [ ] Test: cancelling an already cancelled venta returns error, no stock change

**Test command**: `vendor/bin/phpunit`

---

## Phase 5: Views

### T5.1 — Create `ventas/index.blade.php` — sales history table

**Description**: Create the sales history view extending `x-app-layout`. Include:
- Header with title "Historial de Ventas" and "Nueva Venta" button linking to `route('ventas.pos')`
- Filter form: date inputs `desde`/`hasta`, estado select (todos/completadas/anuladas), "Filtrar" and "Limpiar" buttons
- Flash messages section (`@if(session('success'))` and `@if(session('error'))`)
- Table with columns: #, Fecha (formatted `d/m/Y H:i`), Items (count), Total (`${{ number_format($venta->total, 2) }}`), Procesado por (user name), Estado (badge Completada/Anulada with appropriate colors), Acciones (Ver + Anular with confirm)
- `@forelse` with empty state "No hay ventas registradas."
- Pagination links at bottom
- Anular form uses `confirm()` dialog in Spanish: "¿Anular esta venta? Se restaurará el stock."

**Files to create**:
- `src/resources/views/ventas/index.blade.php`

**Dependencies**: T4.1 (controller index method sends $ventas), T6.1 (route exists, though route testing can use named routes)

**Acceptance criteria**:
- [ ] View extends `x-app-layout`
- [ ] Date filter form submits GET to same URL, preserving query params
- [ ] Table renders all required columns
- [ ] Empty state shows "No hay ventas registradas."
- [ ] Estado uses colored badges (green for completada, red for anulada)
- [ ] Currency uses `${{ number_format($venta->total, 2) }}`
- [ ] "Anular" action only shows for completada ventas
- [ ] Anular uses `confirm()` in Spanish
- [ ] Pagination renders correctly
- [ ] PSR-12 (Blade) — no PHP syntax errors

---

### T5.2 — Create `ventas/pos.blade.php` — POS screen with Alpine.js cart

**Description**: Create the POS view with Alpine.js (`x-data="posApp()"`). Full design (see design §5.1–5.2):
- **Left column** (3/5 on desktop, full on mobile): search input with `@input.debounce.300ms`, results list with product name, price, stock, and "Agregar" button. Handle loading state (`x-show="searching"`), no results, and empty query states.
- **Right column** (2/5 on desktop, full on mobile): cart with items list (product name, price, quantity ± buttons, subtotal, remove button), subtotal/total/descuento fields, pago_con input with cambio display, metodo_pago select, "Cobrar" button (disabled via `:disabled="!canSubmit"` with "Procesando..." state), "Vaciar carrito" button with confirm.
- Alpine.js `posApp()` function with all getters (`subtotal`, `total`, `cambio`, `canSubmit`) and methods (`searchProducts`, `addToCart`, `addFirstResult`, `increaseQty`, `decreaseQty`, `removeFromCart`, `clearCart`, `submitSale`). Submit sends JSON POST with CSRF token.
- Flash messages and validation errors displayed at top.
- CSS: `@push('styles')` for any custom styles if needed.

**Files to create**:
- `src/resources/views/ventas/pos.blade.php`

**Dependencies**: T4.2 (controller create method returns this view), T4.3 (store method handles POST)

**Acceptance criteria**:
- [ ] View extends `x-app-layout`
- [ ] Alpine.js `posApp()` function defined with all required state and methods
- [ ] Search triggers AJAX to `/productos/search?q=...` with 300ms debounce
- [ ] Add to cart works: increases qty if existing, creates entry if new
- [ ] Quantity ± buttons respect `stock_disponible` limit
- [ ] Remove from cart removes item and recalculates totals
- [ ] Subtotal, total, and cambio computed reactively via Alpine getters
- [ ] `canSubmit` getter returns false when: cart empty, total <= 0, or pago_con < total
- [ ] Cobrar button shows "Procesando..." and disables during submission
- [ ] Submit sends correct JSON payload with CSRF token
- [ ] Vaciar shows confirm dialog in Spanish
- [ ] Responsive: columns stack on mobile (< 768px)
- [ ] Flash error messages display at top of view

---

### T5.3 — Create `ventas/show.blade.php` — receipt with `@media print`

**Description**: Create the receipt/detail view. Include:
- Header with "Venta #N" title, date, "Imprimir" button (`onclick="window.print()"`) and "Volver" button
- Receipt card with: TiendaStock branding, venta ID, fecha, cliente (if present)
- Items table: Producto, Precio, Cant., Subtotal columns
- Totals section: Subtotal, Descuento (if > 0), Impuesto (if > 0), Total (bold), Pagó, Cambio
- Método de pago and "Procesado por" info
- Estado badge: "ANULADA" with red styling if anulada
- Anular button (only if completada) with confirm
- `@push('styles')` with `@media print` that hides sidebar, topbar, buttons (`.no-print`), and shows only the receipt content
- All currency formatted with `${{ number_format($value, 2) }}`

**Files to create**:
- `src/resources/views/ventas/show.blade.php`

**Dependencies**: T4.4 (controller show method exists), T4.5 (cancel method for the anular button)

**Acceptance criteria**:
- [ ] View extends `x-app-layout`
- [ ] "Imprimir" button executes `window.print()`
- [ ] `@media print` styles hide navigation, show only receipt
- [ ] Items table renders correctly
- [ ] Descuento/Impuesto rows only show when > 0
- [ ] Estado badge shows "ANULADA" with red styling for anulada ventas
- [ ] Anular button only visible for completada ventas
- [ ] All monetary values use `${{ number_format(..., 2) }}`
- [ ] "Procesado por" shows user name

---

## Phase 6: Routing & Navigation

### T6.1 — Add routes in web.php with role middleware

**Description**: Inside the existing `Route::middleware('auth')->group(...)` in `routes/web.php`, add a new route group with `middleware('role:ADMIN,Ventas')` containing all 5 venta routes. **Order matters**: `GET /ventas/pos` MUST be defined BEFORE `GET /ventas/{venta}` to prevent "pos" from being interpreted as a venta ID.

Routes:
| Método | URI | Controller@action | Name |
|--------|-----|-------------------|------|
| GET | `/ventas` | VentaController@index | ventas.index |
| GET | `/ventas/pos` | VentaController@create | ventas.pos |
| POST | `/ventas` | VentaController@store | ventas.store |
| GET | `/ventas/{venta}` | VentaController@show | ventas.show |
| POST | `/ventas/{venta}/cancel` | VentaController@cancel | ventas.cancel |

Add `use App\Http\Controllers\VentaController;` import at top.

**Files to modify**:
- `src/routes/web.php`

**Dependencies**: T4.5 (VentaController complete with all methods)

**Acceptance criteria**:
- [ ] Import for `VentaController` added
- [ ] `/ventas/pos` route defined BEFORE `/ventas/{venta}`
- [ ] All 5 routes registered with correct method, URI, action, and name
- [ ] Route group uses `middleware('role:ADMIN,Ventas')`
- [ ] Route group is INSIDE the `auth` middleware group
- [ ] `php artisan route:list` shows all routes with correct middleware
- [ ] Existing routes (categorias, productos, admin) are NOT affected

---

### T6.2 — Add Ventas link in sidebar + dashboard actions

**Description**: 
1. In `sidebar.blade.php` (both desktop and mobile sections), add a "Ventas" nav link AFTER the Dashboard link and BEFORE the `@if(in_array(...Control Stock))` block. The link must be wrapped in `@if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))`. Use a shopping cart SVG icon. Active state uses `request()->routeIs('ventas.*')`.
2. In `dashboard.blade.php`, add a "Nueva Venta (POS)" quick action card inside the existing "Acciones Rápidas" grid, visible only for ADMIN and Ventas roles. Use green color scheme to differentiate it from the existing blue (productos/categorias) and green (usuarios) actions.

**Files to modify**:
- `src/resources/views/layouts/sidebar.blade.php`
- `src/resources/views/dashboard.blade.php`

**Dependencies**: T6.1 (routes must exist for `route('ventas.index')`, `route('ventas.pos')` to work)

**Acceptance criteria**:
- [ ] Sidebar link "Ventas" visible for ADMIN and Ventas roles
- [ ] Sidebar link hidden for Control Stock role
- [ ] Sidebar link active state works with `routeIs('ventas.*')`
- [ ] Sidebar link appears in both desktop and mobile sections
- [ ] Dashboard quick action "Nueva Venta (POS)" visible for ADMIN and Ventas
- [ ] Dashboard quick action hidden for Control Stock
- [ ] Existing sidebar/dashboard links are NOT affected

---

## Phase 7: Tests

### T7.1 — Write Feature test: create sale (successful, insufficient stock, empty cart, invalid payment)

**Description**: Create `VentaControllerTest.php` with the following test methods (tdd: true — write tests before implementing controller):
- `test_ventas_page_loads_for_ventas_role()` — GET /ventas as Ventas → 200
- `test_ventas_page_loads_for_admin_role()` — GET /ventas as ADMIN → 200
- `test_pos_page_loads_for_ventas_role()` — GET /ventas/pos → 200
- `test_can_create_sale_with_valid_data()` — POST /ventas with valid items → redirect ventas.show, verify Venta + VentaItems in DB, stock decremented
- `test_cannot_create_sale_with_empty_cart()` — POST /ventas with items=[] → validation error
- `test_cannot_create_sale_with_insufficient_stock()` — POST /ventas with cantidad > stock → validation error, stock unchanged
- `test_cannot_create_sale_with_pago_menor_a_total()` — POST /ventas with pago_con < total → validation error
- `test_store_copies_precio_unitario_at_moment_of_sale()` — create venta, change product price after, verify venta_items.precio_unitario retains original value
- `test_store_uses_transaction_rollback_on_failure()` — POST with mixed items (one valid, one no stock) → rollback, no Venta created
- `test_guest_redirected_from_ventas_routes()` — GET /ventas without auth → redirect /login

Use `RefreshDatabase` trait, `User::factory()->ventas()->create()` for Ventas role, `User::factory()->admin()->create()` for ADMIN.

**Files to create**:
- `src/tests/Feature/VentaControllerTest.php`

**Dependencies**: All Phase 0–6 (full system must be implemented for tests to pass)

**Acceptance criteria**:
- [ ] All test methods pass with `vendor/bin/phpunit`
- [ ] Tests cover: successful sale, insufficient stock, empty cart, pago_con < total, price immutability, transaction rollback, guest redirect
- [ ] Uses `RefreshDatabase` for clean state
- [ ] Uses User factories with appropriate role states
- [ ] Verifies DB state after operations (`assertDatabaseHas`, `assertDatabaseMissing`)
- [ ] Verifies stock values with `$producto->refresh(); assertEquals()`
- [ ] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

### T7.2 — Write Feature test: cancel sale (successful cancel, already cancelled)

**Description**: Create `VentaCancelTest.php` with:
- `test_can_cancel_completed_sale()` — Create venta with items via DB, POST cancel → estado = 'anulada', stock restored, redirect to ventas.index
- `test_cannot_cancel_already_cancelled_sale()` — Cancel same venta twice → second call returns error flash, stock unchanged
- `test_cancel_restores_correct_stock_amounts()` — Create venta with multiple items with different quantities, verify each producto's stock is restored correctly
- `test_cancel_is_atomic()` — Simulate failure during cancel (mock, or verify structure is transactional)
- `test_cancel_requires_auth()` — POST cancel without auth → redirect login
- `test_cancel_forbidden_for_control_stock()` — POST cancel as Control Stock → 403

**Files to create**:
- `src/tests/Feature/VentaCancelTest.php`

**Dependencies**: All Phase 0–6 (full system implemented)

**Acceptance criteria**:
- [ ] All test methods pass
- [ ] Tests cover: successful cancel, already cancelled (idempotent), stock verification, auth check, role check
- [ ] Uses `RefreshDatabase`
- [ ] Verifies stock restoration with exact values
- [ ] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

### T7.3 — Write Feature test: authorization (guest, Control Stock, Ventas, ADMIN)

**Description**: Create `VentaAuthorizationTest.php` (or add to existing test files) covering:
- `test_guest_cannot_access_any_ventas_route()` — All 5 routes return redirect to /login for guest
- `test_control_stock_cannot_access_any_ventas_route()` — All 5 routes return 403 for Control Stock
- `test_ventas_can_access_all_ventas_routes()` — All GET routes return 200 for Ventas; POST store returns redirect with valid data; POST cancel returns redirect
- `test_admin_can_access_all_ventas_routes()` — Same as above for ADMIN
- `test_sidebar_link_hidden_for_control_stock()` — Render dashboard sidebar as Control Stock, assert "Ventas" not visible
- `test_sidebar_link_shown_for_ventas()` — Render dashboard sidebar as Ventas, assert "Ventas" visible
- `test_sidebar_link_shown_for_admin()` — Render dashboard sidebar as ADMIN, assert "Ventas" visible

**Files to create**:
- `src/tests/Feature/VentaAuthorizationTest.php`

**Dependencies**: All Phase 0–6

**Acceptance criteria**:
- [ ] All test methods pass
- [ ] Tests cover: guest, Control Stock (403), Ventas (200), ADMIN (200) for each route
- [ ] Sidebar visibility tests use `assertSee()` / `assertDontSee()`
- [ ] Uses `RefreshDatabase`
- [ ] PSR-12 passes

**Test command**: `vendor/bin/phpunit`

---

## Review Workload Forecast

- **Estimated change lines**: ~1300–1600 lines (13 new files, 5 modified files including migrations, models, controller, views, factories, tests, routes, sidebar, dashboard)
- **Chained PRs recommended**: Yes
- **400-line budget risk**: High — this change is 3–4× the 400-line budget
- **Decision needed before apply**: Yes — define PR splitting strategy. Recommended split:
  - **PR 1** (Foundation): Migrations + Models + Factories + Producto relation (~250 lines)
  - **PR 2** (Backend): StoreVentaRequest + VentaController (all methods) + Routes (~350 lines)
  - **PR 3** (Frontend): All 3 Blade views + Sidebar + Dashboard (~450 lines)
  - **PR 4** (Tests): All test files (~400 lines)
