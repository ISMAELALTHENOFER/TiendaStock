# Exploration: Módulo de Ventas (POS)

## Current State

TiendaStock is a Laravel 12 + Blade + Tailwind CSS 3 + Alpine.js inventory management app for a clothing store. Currently:

- **No sales tracking exists at all.** The app handles products, categories, and users — but once a product is created, there is no way to record a sale, deduct stock, or generate any financial records.
- **Product stock** is a simple `cantidad` (integer) field on the `productos` table. There is no transactional history of stock changes.
- **The Ventas role** is defined (`User::ROLE_VENTAS = 'Ventas'`) and has helper methods (`isVentas()`, `hasRole()`), but has **NO routes assigned** in `web.php`. A user with the Ventas role can log in and see the dashboard, but cannot access any functional section (productos, categorías, admin users). They effectively have a dead-end account.
- **Auth** uses session-based Breeze with the `CheckRole` middleware supporting parameterized roles.
- **Tech stack:** PHP 8.2, Blade with `x-app-layout` component pattern, inline Alpine.js for interactivity, no Livewire, no real-time features.
- **TDD is strict** (`tdd: true` in config), PHPUnit with SQLite in-memory for feature tests, `RefreshDatabase` trait.

## Database Schema Analysis

### Existing tables

| Table | Key columns | Notes |
|-------|-------------|-------|
| `users` | id, name, username, email, password, role (string: ADMIN/Ventas/Control Stock) | No FK to sales |
| `categorias` | id, nombre, descripcion | Product categories, hierarchical via parent? No, flat |
| `productos` | id, nombre, descripcion, categoria_id (FK), precio_compra, precio_venta, cantidad (int), talle, color, activo | Stock is a single integer; no per-variant or per-warehouse |
| `password_reset_tokens` | Standard | — |
| `sessions` | Standard | — |
| `cache`, `jobs` | Standard | — |

### Missing (needs to be created)

The following tables are essential for a professional sales module:

#### `ventas` (sales header)

| Column | Type | Purpose |
|--------|------|---------|
| id | bigIncrements | PK |
| user_id | FK→users | Who processed the sale |
| cliente_nombre | string, nullable | Optional customer name for receipt |
| subtotal | decimal(10,2) | Sum of items before discount |
| descuento | decimal(10,2), default 0 | Discount applied to the whole sale |
| impuesto | decimal(10,2), default 0 | Tax amount (if needed) |
| total | decimal(10,2) | Final amount |
| pago_con | decimal(10,2) | Amount the customer paid |
| cambio | decimal(10,2) | Change returned |
| metodo_pago | string | cash, card, transfer, etc. |
| estado | string | completada, anulada, pendiente |
| created_at | timestamp | Date/time of sale |
| updated_at | timestamp | — |

#### `venta_items` (sale line items)

| Column | Type | Purpose |
|--------|------|---------|
| id | bigIncrements | PK |
| venta_id | FK→ventas (cascade) | Parent sale |
| producto_id | FK→productos | Product sold |
| cantidad | integer | Quantity |
| precio_unitario | decimal(10,2) | Price at time of sale (in case price changes later) |
| subtotal | decimal(10,2) | cantidad × precio_unitario |

This normalizes the schema. Storing `precio_unitario` on the line item is critical for historical accuracy — if product prices change later, past sales must still reflect the original price.

#### `metodos_pago` (optional, reference data)

If payment methods need to be configurable, a small reference table. But an enum or config constant is simpler for this project scope.

### Stock tracking consideration

The current `productos.cantidad` is an integer. For a POS, we need **transaction-safe stock deduction**. Options:
1. **Decrement in a DB transaction** (simplest, good for single-server SQLite)
2. **Stock movement log** (`movimientos_stock` table) for audit trail

Given the SQLite constraint and the app size, option 1 with transaction wrapping is the pragmatic choice. An audit log could be added later.

## Reference POS Features (Industry Standards)

Based on Shopify POS, Square, and SumUp patterns:

### MUST have (MVP)
1. **Product search** — quick search by name, SKU, category to add items to cart
2. **Cart/session** — add items, adjust quantities, remove items before finalizing
3. **Stock validation** — warn/block if quantity exceeds available stock
4. **Automatic total calculation** — subtotal → optional discount → total
5. **Customer payment input** — enter amount paid, auto-calculate change
6. **Stock deduction** — decrement `productos.cantidad` on sale confirmation
7. **Sale record** — persist every completed sale with line items
8. **Role-based access** — only ADMIN and Ventas roles

### SHOULD have (professional quality)
9. **Low-stock warning** — visual indicator when stock < threshold during sale
10. **Receipt view** — printable/on-screen receipt after sale
11. **Sales history** — list of past sales with details
12. **Sale cancellation** — cancel a sale, restore stock (with audit trail)
13. **Search with keyboard shortcuts** — professional POS feel (Enter to add, F1/F2 actions)

### COULD have (future)
14. **Multiple payment methods per sale** (split payment)
15. **Customer history**
16. **Daily summary / closing report**
17. **Barcode scanning**
18. **Hold sale / recall sale**

## Technical Approach Recommendation

### Architecture Decision: Alpine.js + Blade (no Livewire)

**Recommendation: Stay with Alpine.js + Blade + AJAX (no Livewire).**

Rationale:
- The project already uses Alpine.js and Blade. Introducing Livewire adds a new technology with no current need.
- The POS cart is a pure client-side state — Alpine.js handles this perfectly with its reactive `x-data` and `x-model`.
- Server communication is minimal: product search (existing AJAX endpoint) and sale submission (single POST).
- Keeps the tech stack simple and consistent.

If the team were building a full multi-step POS with real-time inventory, complex validation, and server-side cart persistence, Livewire would be justified. Here, Alpine is the right fit.

### New Models

```php
// App\Models\Venta
class Venta extends Model {
    protected $fillable = [
        'user_id', 'cliente_nombre', 'subtotal', 'descuento',
        'impuesto', 'total', 'pago_con', 'cambio', 'metodo_pago', 'estado'
    ];

    public function items(): HasMany { return $this->hasMany(VentaItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

// App\Models\VentaItem
class VentaItem extends Model {
    protected $table = 'venta_items';
    protected $fillable = ['venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal'];

    public function venta(): BelongsTo { return $this->belongsTo(Venta::class); }
    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }
}

// App\Models\Producto — add relationship
public function ventaItems(): HasMany { return $this->hasMany(VentaItem::class); }
```

### Migration Strategy

Two migrations:
1. `create_ventas_table` — the sales header
2. `create_venta_items_table` — the line items with FK to ventas and productos

### Controller Architecture

```
app/Http/Controllers/
├── VentaController.php         ← Resource controller plus POS actions
│   ├── index()                 → List sales (with filters)
│   ├── create()                → POS screen (Alpine.js cart)
│   ├── store(Request)          → Save sale, deduct stock (DB transaction)
│   ├── show(Venta)             → Sale detail / receipt
│   └── cancel(Venta)           → Cancel sale, restore stock
```

The `store` method is the critical piece — it MUST run inside a DB transaction:

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    return DB::transaction(function () use ($validated) {
        // 1. Create Venta (header)
        // 2. Loop items, create VentaItem rows
        // 3. Deduct stock from each Producto
        //    (check stock >= cantidad FIRST)
        // 4. If anything fails → transaction rolls back
        // 5. Return success + sale data
    });
}
```

### Stock Deduction — Transaction-Safe

Using `DB::transaction()` with `lockForUpdate()` on the productos row inside the transaction:

```php
$producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);
if ($producto->cantidad < $item['cantidad']) {
    throw new \RuntimeException("Stock insuficiente para: {$producto->nombre}");
}
$producto->decrement('cantidad', $item['cantidad']);
```

In SQLite, `lockForUpdate()` is not truly supported (no row-level locks), but the transaction serialization in SQLite ensures atomicity. This is sufficient for a single-server SQLite app.

### View Architecture

```
resources/views/
├── ventas/
│   ├── index.blade.php         ← Sales history list
│   ├── pos.blade.php           ← POS screen (the star — Alpine.js cart)
│   └── show.blade.php          ← Sale detail / receipt view
```

#### POS Screen Design (`pos.blade.php`)

The POS screen is a single-page layout with three zones:

```
┌─────────────────────────────────────────────────────┐
│  TOPBAR: "Nueva Venta" | Client name | Cancel       │
├─────────────────────┬───────────────────────────────┤
│                     │                               │
│  SEARCH PANEL       │   CART (Alpine.js $store)     │
│  ┌───────────────┐  │   ┌───────────────────────┐   │
│  │ Buscar prod.  │  │   │ Producto   Cant  Precio│   │
│  └───────────────┘  │   │ --------------------- │   │
│                     │   │ Remera M     1   $15  │   │
│  Results grid       │   │ Jean L       2   $40  │   │
│  ┌───┐ ┌───┐ ┌───┐ │   │                       │   │
│  │ P │ │ P │ │ P │ │   │ Subtotal:        $95   │   │
│  │ 1 │ │ 2 │ │ 3 │ │   │ Descuento:       $0    │   │
│  └───┘ └───┘ └───┘ │   │ Total:           $95   │   │
│                     │   │                       │   │
│                     │   │ Pago con: [$100    ]  │   │
│                     │   │ Cambio:   $5           │   │
│                     │   │                       │   │
│                     │   │ [✅ Cobrar] [🗑 Vaciar]│   │
└─────────────────────┴───────────────────────────────┘
```

The cart state lives in an Alpine.js component with `x-data`:

```javascript
{
    cart: [],
    searchQuery: '',
    searchResults: [],
    pagoCon: 0,

    get subtotal() { return this.cart.reduce((sum, item) => sum + item.subtotal, 0); },
    get total() { return this.subtotal - this.descuento + this.impuesto; },
    get cambio() { return Math.max(0, this.pagoCon - this.total); },

    addToCart(producto) { ... },
    removeFromCart(index) { ... },
    updateQuantity(index, qty) { ... },
    searchProducts() { ... },  // AJAX to /productos/search
    submitSale() { ... }       // POST to /ventas
}
```

### Payment Handling

- `metodo_pago` stored as string on the Venta model ('efectivo', 'tarjeta', 'transferencia')
- `pago_con` is the amount tendered by the customer
- `cambio` is auto-calculated: `pago_con - total`
- The POS UI validates: if `pago_con < total`, show a warning and disable the submit button
- For non-cash payments, `pago_con` = `total` and `cambio` = 0

### Receipt/Invoice Generation

- The `show` view (`ventas.show`) acts as the receipt
- Use CSS `@media print` for a printer-friendly layout
- Include: store name, date, items list, totals, change, processed by
- A "Print" button triggers `window.print()`

### Sales History

`ventas.index` is a paginated table with:
- Date, items count, total, processed by, estado
- Search by date range
- Click to view detail
- "Anular" action for admin/Ventas roles

### Access Control

In `web.php`:

```php
Route::middleware(['auth', 'role:ADMIN,Ventas'])->group(function () {
    Route::resource('/ventas', VentaController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/ventas/{venta}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
});
```

Sidebar: Add a "Ventas" link visible only to ADMIN and Ventas roles.

## Risks and Considerations

1. **SQLite concurrency**: SQLite uses file-level locking. If two cashiers process sales simultaneously, one will get a "database is locked" error. For a single-store with one POS terminal this is fine. For multi-terminal, migration to PostgreSQL/MySQL would be needed.

2. **Stock integrity**: The current `cantidad` field is a simple integer. If admin manually edits stock while a sale is in progress, the transaction's `lockForUpdate` won't prevent phantom reads fully in SQLite. Mitigation: wrap in transaction with stock re-verification.

3. **TDD overhead**: Strict TDD means every controller method needs feature tests. The POS controller is complex (cart validation, stock deduction, transaction handling). Tests need to cover: successful sale, insufficient stock, empty cart, invalid quantities, guest access, wrong role access, sale cancellation.

4. **No decimal casting for prices**: The migration schema needs `decimal(10, 2)` for all monetary columns. The existing `productos` table already uses this pattern.

5. **AJAX search already exists**: The `ProductoController@search` endpoint already provides JSON search — this can be reused directly.

6. **Session-based auth**: Since it's session-based, the POS doesn't need OAuth/API tokens. The `auth` middleware + role check is sufficient.

7. **No notifications/events yet**: Stock alerts on low inventory would need events/listeners — medium complexity, deferrable.

## File Inventory — All Files to Create or Modify

### New Files

```
src/app/Models/Venta.php                           — Venta model
src/app/Models/VentaItem.php                       — VentaItem model
src/app/Http/Controllers/VentaController.php       — Sales controller (resource + cancel)
src/app/Http/Requests/StoreVentaRequest.php        — Form request for sale validation
src/database/migrations/YYYY_MM_DD_HHMMSS_create_ventas_table.php
src/database/migrations/YYYY_MM_DD_HHMMSS_create_venta_items_table.php
src/resources/views/ventas/index.blade.php          — Sales history
src/resources/views/ventas/pos.blade.php            — POS screen (Alpine.js cart)
src/resources/views/ventas/show.blade.php           — Sale detail / receipt view
src/tests/Feature/VentaControllerTest.php          — Feature tests for all sale operations
src/tests/Feature/SaleRoleAccessTest.php           — Role access tests for sales
src/tests/Unit/VentaModelTest.php                  — Unit tests for Venta model
src/tests/Unit/VentaItemModelTest.php              — Unit tests for VentaItem model
src/database/factories/VentaFactory.php            — Factory for tests
src/database/factories/VentaItemFactory.php        — Factory for test items
```

### Modified Files

```
src/routes/web.php                                  — Add ventas routes (role:ADMIN,Ventas)
src/resources/views/layouts/sidebar.blade.php       — Add "Ventas" nav link
src/resources/views/dashboard.blade.php             — Add sales stats, Ventas role quick actions
src/app/Models/Producto.php                         — Add ventaItems() relationship
```

### Optional / Nice-to-Have

```
src/app/Http/Controllers/VentaController.php — Add daily report endpoint (future)
```

Total: ~20 files (13 new, 5 modified). This is a substantial change.
