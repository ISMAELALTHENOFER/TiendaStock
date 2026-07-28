# Diseño Técnico — Módulo de Ventas (POS)

**Change**: modulo-ventas  
**Versión**: 1.0  
**Estado**: Aprobado

---

## 1. Architecture Overview

### 1.1 Diagrama de Componentes

```
┌─────────────────────────────────────────────────────────────────────────┐
│                            WEB BROWSER                                  │
│  ┌──────────────────────┐  ┌──────────────────────┐                     │
│  │  ventas/index.blade  │  │   ventas/pos.blade   │                     │
│  │  (Tabla + filtros)   │  │  (Alpine.js cart)    │                     │
│  └──────────┬───────────┘  └──────────┬───────────┘                     │
│             │                         │                                 │
│  ┌──────────▼─────────────────────────▼───────────┐                     │
│  │          ventas/show.blade (recibo)             │                     │
│  └─────────────────────────────────────────────────┘                     │
└──────────────────────────┬──────────────────────────────────────────────┘
                           │ HTTP
                           ▼
┌──────────────────────────────────────────────────────────────────────────┐
│  LARAVEL ROUTING  ───  web.php                                           │
│  Middleware: auth, verified, role:ADMIN,Ventas                            │
├──────────────────────────────────────────────────────────────────────────┤
│  CONTROLLER: VentaController                                             │
│  ┌──────────┬──────────┬──────────┬──────────┬───────────┐              │
│  │  index   │  create  │  store   │   show   │   cancel  │              │
│  │  (GET)   │  (GET)   │  (POST)  │  (GET)   │  (POST)   │              │
│  └────┬─────┴────┬─────┴────┬─────┴────┬─────┴─────┬─────┘              │
│       │          │          │          │           │                     │
│       ▼          ▼          ▼          ▼           ▼                     │
│  ┌────────┐ ┌────────┐ ┌──────────────┐ ┌────────┐ ┌──────────┐        │
│  │ Venta  │ │ Venta  │ │StoreVentaReq │ │ Venta  │ │ Venta    │        │
│  │ (Model)│ │ (Model)│ │ (Form Req)   │ │ (Model)│ │ (Model)  │        │
│  └────────┘ └────────┘ └──────┬───────┘ └────────┘ └──────────┘        │
│                                │                                         │
│  ┌─────────────────────────────▼──────────────────────────────┐         │
│  │              DB::transaction + lockForUpdate                 │         │
│  │              Venta + VentaItem + Producto::decrement         │         │
│  └─────────────────────────────────────────────────────────────┘         │
├──────────────────────────────────────────────────────────────────────────┤
│  MODELOS                                                                │
│  ┌──────────┐  ┌───────────┐  ┌───────────┐  ┌──────────┐              │
│  │  Venta   │  │ VentaItem │  │ Producto  │  │   User   │              │
│  │belongsTo │  │belongsTo  │  │hasMany    │  │hasMany   │              │
│  │ User     │  │ Venta     │  │ VentaItem │  │ Ventas   │              │
│  │hasMany   │  │belongsTo  │  │           │  │          │              │
│  │VentaItem │  │ Producto  │  │           │  │          │              │
│  └──────────┘  └───────────┘  └───────────┘  └──────────┘              │
├──────────────────────────────────────────────────────────────────────────┤
│  BASE DE DATOS (SQLite)                                                  │
│  ┌──────────┐  ┌────────────┐  ┌───────────┐                            │
│  │  ventas  │──│venta_items │  │ productos │                            │
│  │          │  │ FK venta_id│  │ ← decrem  │                            │
│  │estado    │  │ FK prod_id │  │           │                            │
│  │total     │  │ precio_cop │  │ cantidad  │                            │
│  └──────────┘  └────────────┘  └───────────┘                            │
└──────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Principios de Arquitectura

1. **POS client-side con Alpine.js** — el carrito, cálculos de totales y cambio viven enteramente en el navegador. No hay estado de carrito en sesión ni en servidor. Al hacer submit, se envía un JSON con los ítems a `POST /ventas`.
2. **Transacciones atómicas** — toda creación o anulación de venta ocurre dentro de `DB::transaction()` con `lockForUpdate` para evitar race conditions en stock.
3. **Precio inmutable** — `venta_items.precio_unitario` es una copia del precio de venta al momento de la transacción. Si el precio del producto cambia después, la venta registrada conserva el precio original.
4. **Control de acceso granular** — todas las rutas del módulo están protegidas por middleware `role:ADMIN,Ventas`. El sidebar muestra enlaces solo a estos roles.

---

## 2. Data Model

### 2.1 Migración: `create_ventas_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('cliente_nombre')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('pago_con', 10, 2);
            $table->decimal('cambio', 10, 2)->default(0);
            $table->string('metodo_pago', 50);          // efectivo, tarjeta, transferencia
            $table->string('estado', 20)->default('completada'); // completada, anulada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
```

### 2.2 Migración: `create_venta_items_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);  // cantidad × precio_unitario
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_items');
    }
};
```

### 2.3 Modelo: `Venta.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'cliente_nombre',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'pago_con',
        'cambio',
        'metodo_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'     => 'decimal:2',
            'descuento'    => 'decimal:2',
            'impuesto'     => 'decimal:2',
            'total'        => 'decimal:2',
            'pago_con'     => 'decimal:2',
            'cambio'       => 'decimal:2',
            'created_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }

    public function isCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    public function isAnulada(): bool
    {
        return $this->estado === 'anulada';
    }
}
```

### 2.4 Modelo: `VentaItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaItem extends Model
{
    protected $table = 'venta_items';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
```

### 2.5 Modificación al modelo `Producto.php`

Se AGREGA la relación `ventaItems()` y se importa `HasMany`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

// === DENTRO DE LA CLASE ===

public function ventaItems(): HasMany
{
    return $this->hasMany(VentaItem::class);
}
```

### 2.6 Relación en `User.php`

Opcional pero recomendada para consultas:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function ventas(): HasMany
{
    return $this->hasMany(Venta::class);
}
```

### 2.7 Diagrama ER

```
┌───────────────────┐       ┌─────────────────────────┐       ┌───────────────────┐
│      users        │       │        ventas            │       │   venta_items     │
├───────────────────┤       ├─────────────────────────┤       ├───────────────────┤
│ id (PK)           │◄──────│ user_id (FK)            │       │ id (PK)           │
│ name              │       │ id (PK)                 │       │ venta_id (FK)     │────► ventas.id
│ email             │       │ cliente_nombre (null)   │       │ producto_id (FK)  │────► productos.id
│ role              │       │ subtotal (dec 10,2)     │       │ cantidad (int)    │
│ ...               │       │ descuento (dec 10,2)    │       │ precio_unitario   │
└───────────────────┘       │ impuesto (dec 10,2)     │       │ subtotal (dec 10,2)│
                            │ total (dec 10,2)        │       │ created_at        │
                            │ pago_con (dec 10,2)     │       │ updated_at        │
                            │ cambio (dec 10,2)       │       └───────────────────┘
                            │ metodo_pago (varchar50) │
                            │ estado (varchar20)       │
                            │ created_at              │
                            │ updated_at              │
                            └─────────────────────────┘
```

---

## 3. Sequence Diagrams

### 3.1 `POST /ventas` — Crear venta (flujo principal)

```
Cliente (Alpine.js)          VentaController           DB (SQLite)              Producto
       │                           │                       │                      │
       │  POST /ventas             │                       │                      │
       │  {items:[{id,qty}],       │                       │                      │
       │   pago_con, metodo_pago,  │                       │                      │
       │   descuento, impuesto}    │                       │                      │
       │──────────────────────────>│                       │                      │
       │                           │                       │                      │
       │                           │  StoreVentaRequest    │                      │
       │                           │  → validate()        │                      │
       │                           │                       │                      │
       │                           │  DB::beginTransaction │                      │
       │                           │──────────────────────>│                      │
       │                           │                       │                      │
       │                           │  Venta::create([      │                      │
       │                           │    user_id,auth()->id │                      │
       │                           │    subtotal,total...  │                      │
       │                           │  ])                   │                      │
       │                           │──────────────────────>│                      │
       │                           │  ← venta (created)    │                      │
       │                           │<──────────────────────│                      │
       │                           │                       │                      │
       │                           │  LOOP items[]:        │                      │
       │                           │    Producto::where(    │                      │
       │                           │      id,item.id)       │                      │
       │                           │    →lockForUpdate()    │                      │
       │                           │    →firstOrFail()     │                      │
       │                           │──────────────────────>│                      │
       │                           │  ← producto           │                      │
       │                           │<──────────────────────│                      │
       │                           │                       │                      │
       │                           │    if item.cantidad >  │                      │
       │                           │       producto.cantidad│                      │
       │                           │    → throw Exception   │                      │
       │                           │    "Stock insuficiente │                      │
       │                           │     para {producto}"   │                      │
       │                           │                       │                      │
       │                           │    VentaItem::create([ │                      │
       │                           │      venta_id,         │                      │
       │                           │      producto_id,      │                      │
       │                           │      cantidad,         │                      │
       │                           │      precio_unitario   │                      │
       │                           │        = prod.precio_  │                      │
       │                           │          venta,        │                      │
       │                           │      subtotal          │                      │
       │                           │        = cant*precio   │                      │
       │                           │    ])                  │                      │
       │                           │──────────────────────>│                      │
       │                           │                       │                      │
       │                           │    producto->decrement(│                      │
       │                           │      'cantidad',       │                      │
       │                           │      item.cantidad     │                      │
       │                           │    )                   │                      │
       │                           │───────────────────────>│────────────────────>│
       │                           │                       │                      │
       │                           │  DB::commit            │                      │
       │                           │──────────────────────>│                      │
       │                           │                       │                      │
       │  ← redirect(ventas.show)  │                       │                      │
       │    + flash('success',     │                       │                      │
       │      'Venta #N creada')   │                       │                      │
       │<──────────────────────────│                       │                      │
```

### 3.2 `POST /ventas/{venta}/cancel` — Anular venta

```
Admin/Ventas            VentaController             DB               Producto
     │                        │                     │                   │
     │  POST /ventas/{id}/    │                     │                   │
     │  cancel                │                     │                   │
     │───────────────────────>│                     │                   │
     │                        │                     │                   │
     │                        │  verificar estado    │                   │
     │                        │  === 'completada'    │                   │
     │                        │                     │                   │
     │                        │  DB::transaction     │                   │
     │                        │────────────────────>│                   │
     │                        │                     │                   │
     │                        │  $venta->items      │                   │
     │                        │────────────────────>│                   │
     │                        │  ← items[]          │                   │
     │                        │<────────────────────│                   │
     │                        │                     │                   │
     │                        │  LOOP items:        │                   │
     │                        │    Producto::find(   │                   │
     │                        │      item.prod_id)  │                   │
     │                        │    ->increment(      │                   │
     │                        │      'cantidad',     │                   │
     │                        │      item.cantidad   │                   │
     │                        │    )                 │                   │
     │                        │─────────────────────>│──────────────────>│
     │                        │                     │                   │
     │                        │  $venta->update([    │                   │
     │                        │    'estado' =>       │                   │
     │                        │    'anulada'         │                   │
     │                        │  ])                  │                   │
     │                        │────────────────────>│                   │
     │                        │                     │                   │
     │                        │  DB::commit          │                   │
     │                        │────────────────────>│                   │
     │                        │                     │                   │
     │  ← redirect(ventas.ind)│                     │                   │
     │    + flash('success',  │                     │                   │
     │    'Venta anulada')    │                     │                   │
     │<───────────────────────│                     │                   │
```

### 3.3 `GET /ventas/pos` — Renderizar POS

```
Ventas/Admin          VentaController@create        DB
     │                       │                      │
     │  GET /ventas/pos      │                      │
     │──────────────────────>│                      │
     │                       │                      │
     │                       │  No DB query —       │
     │                       │  los productos se    │
     │                       │  cargan via AJAX     │
     │                       │                      │
     │  ← view('ventas.pos') │                      │
     │    (Alpine.js cart    │                      │
     │     empty, busca-     │                      │
     │     dor, métodos)     │                      │
     │<──────────────────────│                      │
```

### 3.4 `GET /productos/search` — Búsqueda AJAX para POS

```
Alpine.js              ProductoController@search      DB
   │                          │                       │
   │  GET /productos/         │                       │
   │  search?q=remera         │                       │
   │─────────────────────────>│                       │
   │                          │                       │
   │                          │  Producto::where(     │
   │                          │    nombre LIKE '%remera%')
   │                          │  ->orWhere(...)       │
   │                          │  ->limit(10)          │
   │                          │  ->get()              │
   │                          │──────────────────────>│
   │                          │  ← productos[]        │
   │                          │<──────────────────────│
   │                          │                       │
   │  ← JSON [{id, nombre,    │                       │
   │    precio_venta, stock,  │                       │
   │    talle, color}]        │                       │
   │<─────────────────────────│                       │
```

### 3.5 Error Flow: Stock Insuficiente

```
Alpine.js              VentaController              DB           Producto
   │                         │                      │               │
   │  POST /ventas           │                      │               │
   │────────────────────────>│                      │               │
   │                         │                      │               │
   │                         │  StoreVentaRequest    │               │
   │                         │  → validate() (OK)    │               │
   │                         │                      │               │
   │                         │  DB::transaction      │               │
   │                         │─────────────────────>│               │
   │                         │                      │               │
   │                         │  Venta::create(...)   │               │
   │                         │                      │               │
   │                         │  LOOP items:          │               │
   │                         │    Producto::lockForUpdate()->first()
   │                         │─────────────────────>│──────────────>│
   │                         │  ← prod: stock=2     │               │
   │                         │<─────────────────────│──────────────│
   │                         │                      │               │
   │                         │  item.cantidad=5     │               │
   │                         │  5 > 2 → EXCEPTION!  │               │
   │                         │                      │               │
   │                         │  DB::rollback         │               │
   │                         │─────────────────────>│               │
   │                         │                      │               │
   │  ← redirect(ventas.pos) │                      │               │
   │    + flash('error',     │                      │               │
   │    'Stock insuficiente  │                      │               │
   │    para Remera M')      │                      │               │
   │<────────────────────────│                      │               │
```

### 3.6 Error Flow: `pago_con < total`

```
Alpine.js                         VentaController
   │                                     │
   │  POST /ventas                       │
   │  pago_con: 50                       │
   │  total: 100                         │
   │────────────────────────────────────>│
   │                                     │
   │  StoreVentaRequest:                 │
   │  validate() → FALLA                 │
   │  'pago_con' debe ser ≥ 'total'      │
   │                                     │
   │  ← redirect()->back()              │
   │    + withErrors(['pago_con' =>      │
   │      'El monto recibido debe ser    │
   │       mayor o igual al total'])     │
   │<────────────────────────────────────│
```

> **NOTA**: Esta validación también DEBE existir en el frontend (Alpine.js): el botón "Cobrar" se deshabilita mientras `pago_con < total`. La validación del servidor es la autoritativa.

---

## 4. Controller Design

### 4.1 `VentaController` — Definición completa

```php
<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Http\Requests\StoreVentaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Listado de ventas con filtros.
     * GET /ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with('user', 'items')
            ->orderBy('created_at', 'desc');

        // Filtro por rango de fechas
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ventas = $query->paginate(15);

        return view('ventas.index', compact('ventas'));
    }

    /**
     * Pantalla POS (punto de venta).
     * GET /ventas/pos
     */
    public function create()
    {
        return view('ventas.pos');
    }

    /**
     * Registrar una nueva venta.
     * POST /ventas
     */
    public function store(StoreVentaRequest $request)
    {
        $validated = $request->validated();

        $venta = DB::transaction(function () use ($validated) {
            // 1. Crear cabecera de venta
            $venta = Venta::create([
                'user_id'       => auth()->id(),
                'cliente_nombre'=> $validated['cliente_nombre'] ?? null,
                'subtotal'      => $validated['subtotal'],
                'descuento'     => $validated['descuento'] ?? 0,
                'impuesto'      => $validated['impuesto'] ?? 0,
                'total'         => $validated['total'],
                'pago_con'      => $validated['pago_con'],
                'cambio'        => $validated['pago_con'] - $validated['total'],
                'metodo_pago'   => $validated['metodo_pago'],
                'estado'        => 'completada',
            ]);

            // 2. Procesar cada ítem del carrito
            foreach ($validated['items'] as $item) {
                // lockForUpdate: bloquea el registro hasta que la transacción termine
                $producto = Producto::where('id', $item['producto_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Validar stock (doble validación, la primera está en el Form Request)
                if ($item['cantidad'] > $producto->cantidad) {
                    throw new \RuntimeException(
                        "Stock insuficiente para {$producto->nombre}. " .
                        "Disponible: {$producto->cantidad}, solicitado: {$item['cantidad']}"
                    );
                }

                // Crear ítem de venta con copia del precio
                $venta->items()->create([
                    'producto_id'     => $producto->id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal'        => $item['cantidad'] * $producto->precio_venta,
                ]);

                // Deducir stock
                $producto->decrement('cantidad', $item['cantidad']);
            }

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)
            ->with('success', "Venta #{$venta->id} registrada correctamente.");
    }

    /**
     * Mostrar detalle de venta (recibo).
     * GET /ventas/{venta}
     */
    public function show(Venta $venta)
    {
        $venta->load('user', 'items.producto');

        return view('ventas.show', compact('venta'));
    }

    /**
     * Anular una venta y restaurar stock.
     * POST /ventas/{venta}/cancel
     */
    public function cancel(Venta $venta)
    {
        if ($venta->isAnulada()) {
            return redirect()->back()
                ->with('error', 'Esta venta ya fue anulada anteriormente.');
        }

        DB::transaction(function () use ($venta) {
            // Restaurar stock para cada ítem
            foreach ($venta->items as $item) {
                Producto::where('id', $item->producto_id)
                    ->lockForUpdate()
                    ->increment('cantidad', $item->cantidad);
            }

            // Marcar venta como anulada
            $venta->update(['estado' => 'anulada']);
        });

        return redirect()->route('ventas.index')
            ->with('success', "Venta #{$venta->id} anulada. Stock restaurado.");
    }
}
```

### 4.2 `StoreVentaRequest` — Validación

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización la maneja el middleware role
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1',
            'items.*.producto_id'=> 'required|integer|exists:productos,id',
            'items.*.cantidad'   => 'required|integer|min:1',

            'cliente_nombre'     => 'nullable|string|max:255',
            'subtotal'           => 'required|numeric|min:0',
            'descuento'          => 'nullable|numeric|min:0',
            'impuesto'           => 'nullable|numeric|min:0',
            'total'              => 'required|numeric|min:0',
            'pago_con'           => 'required|numeric|min:0',
            'metodo_pago'        => 'required|string|in:efectivo,tarjeta,transferencia',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Debe agregar al menos un producto al carrito.',
            'items.min'                   => 'Debe agregar al menos un producto al carrito.',
            'items.*.producto_id.required' => 'Cada ítem debe tener un producto.',
            'items.*.producto_id.exists'  => 'Uno de los productos no existe en el sistema.',
            'items.*.cantidad.required'   => 'Cada ítem debe tener una cantidad.',
            'items.*.cantidad.min'        => 'La cantidad debe ser al menos 1.',
            'total.required'              => 'El total es obligatorio.',
            'total.min'                   => 'El total debe ser mayor a cero.',
            'pago_con.required'           => 'Debe ingresar el monto recibido.',
            'pago_con.min'                => 'El monto recibido debe ser mayor a cero.',
            'metodo_pago.required'        => 'Debe seleccionar un método de pago.',
            'metodo_pago.in'              => 'Método de pago inválido. Use efectivo, tarjeta o transferencia.',
        ];
    }

    /**
     * Validación adicional después de las reglas base.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            // Validar que pago_con >= total
            if (isset($data['pago_con'], $data['total'])) {
                if ((float) $data['pago_con'] < (float) $data['total']) {
                    $validator->errors()->add(
                        'pago_con',
                        'El monto recibido debe ser mayor o igual al total de la venta.'
                    );
                }
            }

            // Validar stock suficiente para cada item (primera línea de defensa)
            if (isset($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    $producto = \App\Models\Producto::find($item['producto_id']);
                    if ($producto && $item['cantidad'] > $producto->cantidad) {
                        $validator->errors()->add(
                            "items.{$index}.cantidad",
                            "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->cantidad}"
                        );
                    }
                }
            }
        });
    }
}
```

### 4.3 Sobre `lockForUpdate`

`lockForUpdate()` es un lock pesimista a nivel de fila. En SQLite, que no tiene MVCC como PostgreSQL/MySQL, el comportamiento es diferente:

- **SQLite**: `lockForUpdate()` se traduce a `SELECT ... FOR UPDATE`. SQLite serializa transacciones de escritura. Mientras una transacción tiene un `SELECT ... FOR UPDATE` activo, otras escrituras en esa fila deben esperar.
- **En la práctica**: Dado que SQLite usa locking a nivel de base de datos para escritura, tener `lockForUpdate` dentro de la transacción es correcto y provee la seguridad necesaria. Si hubiera dos solicitudes simultáneas (improbable en una sola caja), la segunda espera a que la primera transacción termine.
- **Ubicación**: `lockForUpdate` se aplica DENTRO de `DB::transaction()` y ANTES de hacer el `decrement()`. Esto asegura que ningún otro proceso lea o modifique el registro de producto mientras se procesa la venta.

### 4.4 Manejo de errores en `store`

| Escenario | Respuesta |
|-----------|-----------|
| Validación falla (Form Request) | `422` redirect back con errores |
| Stock insuficiente (dentro de transacción) | `RuntimeException` → rollback automático → redirect back con `flash('error', ...)` |
| Producto no existe (eliminado entre validación y store) | `findOrFail` → `ModelNotFoundException` → 404 |

Para capturar el `RuntimeException` dentro de la transacción y mostrar un mensaje amigable:

```php
try {
    $venta = DB::transaction(function () use ($validated) { ... });
} catch (\RuntimeException $e) {
    return redirect()->route('ventas.create')
        ->with('error', $e->getMessage());
}
```

---

## 5. View Design

### 5.1 `ventas/pos.blade.php` — Pantalla POS con Alpine.js

**Estructura general**:

```blade
<x-app-layout>
    <x-slot name="header">
        <!-- Título + botón Volver -->
    </x-slot>

    <div x-data="posApp()" class="...">
        @if(session('error'))
            <!-- Alerta de error -->
        @endif

        <!-- Layout responsive: columnas en md+, apilado en mobile -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- COLUMNA IZQUIERDA (3/5): Búsqueda y resultados -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Buscador -->
                <div>
                    <input type="text" x-model="searchQuery"
                           @input.debounce.300ms="searchProducts"
                           @keydown.enter.prevent="addFirstResult"
                           placeholder="🔍 Buscar productos por nombre..."
                           class="w-full ...">
                </div>

                <!-- Resultados de búsqueda -->
                <div x-show="searchResults.length > 0" class="...">
                    <template x-for="product in searchResults" :key="product.id">
                        <div class="flex items-center justify-between p-3 ...">
                            <div>
                                <p class="font-semibold" x-text="product.nombre"></p>
                                <p class="text-sm text-gray-500"
                                   x-text="'$' + product.precio_venta + ' | Stock: ' + product.cantidad"></p>
                            </div>
                            <button @click="addToCart(product)"
                                    class="bg-brand-300 text-white px-3 py-1 rounded ...">
                                + Agregar
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Sin resultados -->
                <div x-show="searchQuery.length >= 2 && searchResults.length === 0 && !searching"
                     class="text-center p-8 text-gray-500">
                    No se encontraron productos para "<span x-text="searchQuery"></span>"
                </div>

                <!-- Loading -->
                <div x-show="searching" class="text-center p-4 text-gray-400">
                    Buscando...
                </div>
            </div>

            <!-- COLUMNA DERECHA (2/5): Carrito -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border p-4">
                    <h3 class="font-bold text-lg mb-4">🛒 Carrito</h3>

                    <!-- Carrito vacío -->
                    <div x-show="cart.length === 0" class="text-center py-8 text-gray-400">
                        El carrito está vacío. Busque productos para agregar.
                    </div>

                    <!-- Items del carrito -->
                    <template x-for="(item, index) in cart" :key="item.producto_id">
                        <div class="flex items-center justify-between py-2 border-b">
                            <div class="flex-1">
                                <p x-text="item.nombre" class="font-medium"></p>
                                <p class="text-sm text-gray-500" x-text="'$' + item.precio_venta + ' c/u'"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decreaseQty(index)" class="...">−</button>
                                <input type="number" x-model="item.cantidad"
                                       @input.debounce="updateCart"
                                       min="1" :max="item.stock_disponible"
                                       class="w-12 text-center ...">
                                <button @click="increaseQty(index)" class="...">+</button>
                            </div>
                            <p class="font-bold w-20 text-right" x-text="'$' + (item.cantidad * item.precio_venta).toFixed(2)"></p>
                            <button @click="removeFromCart(index)" class="text-red-500 ml-2">✕</button>
                        </div>
                    </template>

                    <!-- Totales -->
                    <div class="mt-4 space-y-2 pt-4 border-t">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span x-text="'$' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Descuento:</span>
                            <input type="number" x-model="descuento"
                                   @input="updateTotals"
                                   class="w-24 text-right border rounded px-2" step="0.01" min="0">
                        </div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span x-text="'$' + total.toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Pago -->
                    <div class="mt-4 space-y-2">
                        <div>
                            <label class="block text-sm font-medium">Método de pago</label>
                            <select x-model="metodo_pago" class="w-full border rounded px-3 py-2">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Pago con</label>
                            <input type="number" x-model="pago_con"
                                   @input="updateTotals"
                                   class="w-full border rounded px-3 py-2" step="0.01" min="0">
                        </div>
                        <div class="flex justify-between text-lg" x-show="pago_con > 0">
                            <span>Cambio:</span>
                            <span class="font-bold" x-text="'$' + cambio.toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-6 flex gap-2">
                        <button @click="submitSale"
                                :disabled="!canSubmit"
                                class="flex-1 bg-green-600 text-white py-3 rounded-lg font-bold disabled:opacity-50"
                                x-text="submitting ? 'Procesando...' : '✓ Cobrar'">
                        </button>
                        <button @click="clearCart"
                                class="px-4 py-3 bg-gray-200 rounded-lg font-medium text-gray-700">
                            🗑 Vaciar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
```

### 5.2 Alpine.js Data Model (`posApp()`)

```javascript
function posApp() {
    return {
        // Estado de búsqueda
        searchQuery: '',
        searchResults: [],
        searching: false,

        // Carrito
        cart: [],           // [{producto_id, nombre, precio_venta, cantidad, stock_disponible}]

        // Pago
        metodo_pago: 'efectivo',
        pago_con: 0,
        descuento: 0,
        impuesto: 0,

        // UI State
        submitting: false,

        // Getters computados
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.cantidad * item.precio_venta), 0);
        },

        get total() {
            return Math.max(0, this.subtotal - this.descuento + this.impuesto);
        },

        get cambio() {
            return Math.max(0, parseFloat(this.pago_con) - this.total);
        },

        get canSubmit() {
            return this.cart.length > 0
                && this.total > 0
                && parseFloat(this.pago_con) >= this.total;
        },

        // Métodos
        async searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const response = await fetch(`/productos/search?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await response.json();
            } catch (e) {
                console.error('Error en búsqueda:', e);
                this.searchResults = [];
            } finally {
                this.searching = false;
            }
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.producto_id === product.id);

            if (existing) {
                if (existing.cantidad < product.cantidad) {
                    existing.cantidad++;
                } else {
                    alert('Stock máximo alcanzado para este producto.');
                }
            } else {
                this.cart.push({
                    producto_id: product.id,
                    nombre: product.nombre,
                    precio_venta: parseFloat(product.precio_venta),
                    cantidad: 1,
                    stock_disponible: product.cantidad,
                });
            }

            this.searchQuery = '';
            this.searchResults = [];
        },

        addFirstResult() {
            if (this.searchResults.length > 0) {
                this.addToCart(this.searchResults[0]);
            }
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.cantidad < item.stock_disponible) {
                item.cantidad++;
            }
        },

        decreaseQty(index) {
            const item = this.cart[index];
            if (item.cantidad > 1) {
                item.cantidad--;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            if (this.cart.length === 0) return;
            if (confirm('¿Vaciar el carrito? Se perderán los productos agregados.')) {
                this.cart = [];
                this.pago_con = 0;
                this.descuento = 0;
                this.impuesto = 0;
            }
        },

        async submitSale() {
            if (!this.canSubmit || this.submitting) return;

            this.submitting = true;

            const payload = {
                items: this.cart.map(item => ({
                    producto_id: item.producto_id,
                    cantidad: item.cantidad,
                })),
                subtotal: this.subtotal,
                descuento: this.descuento,
                impuesto: this.impuesto,
                total: this.total,
                pago_con: parseFloat(this.pago_con),
                metodo_pago: this.metodo_pago,
            };

            try {
                const response = await fetch('/ventas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    const data = await response.json();
                    if (data.errors) {
                        alert(Object.values(data.errors).flat().join('\n'));
                    }
                }
            } catch (e) {
                alert('Error al procesar la venta. Intente nuevamente.');
            } finally {
                this.submitting = false;
            }
        },
    };
}
```

**Consideración de carga**: Este script DEBE colocarse en la vista o en un archivo JS dedicado. Recomiendo crear `resources/js/pos.js` y cargarlo solo en la vista POS:

```blade
@push('scripts')
    <script src="{{ asset('js/pos.js') }}"></script>
@endpush
```

Si no se usa `@push`, el script puede ir directamente en la vista dentro de una etiqueta `<script>` al final.

### 5.3 `ventas/index.blade.php` — Historial de ventas

```
<x-app-layout>
    <x-slot name="header">
        <!-- Título + botón "Nueva Venta" (redirige a POS) -->
    </x-slot>

    <!-- Filtros de fecha y estado -->
    <form method="GET" class="flex gap-4 mb-6">
        <input type="date" name="desde" value="{{ request('desde') }}" class="...">
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="...">
        <select name="estado" class="...">
            <option value="">Todos los estados</option>
            <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
            <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anuladas</option>
        </select>
        <button type="submit" class="bg-brand-300 text-white px-4 py-2 rounded">Filtrar</button>
        <a href="{{ route('ventas.index') }}" class="bg-gray-200 px-4 py-2 rounded">Limpiar</a>
    </form>

    @if(session('success'))
        <!-- Flash success -->
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Procesado por</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                <tr>
                    <td class="font-bold">{{ $venta->id }}</td>
                    <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->items->count() }}</td>
                    <td>${{ number_format($venta->total, 2) }}</td>
                    <td>{{ $venta->user->name }}</td>
                    <td>
                        <span class="px-2 py-1 rounded-full text-xs font-bold
                            {{ $venta->isCompletada() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $venta->isCompletada() ? 'Completada' : 'Anulada' }}
                        </span>
                    </td>
                    <td class="flex gap-2">
                        <a href="{{ route('ventas.show', $venta) }}" class="text-brand-300">Ver</a>
                        @if($venta->isCompletada())
                        <form action="{{ route('ventas.cancel', $venta) }}" method="POST"
                              onsubmit="return confirm('¿Anular esta venta? Se restaurará el stock.')">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Anular</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-500">
                        No hay ventas registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $ventas->links() }}
        </div>
    </div>
</x-app-layout>
```

### 5.4 `ventas/show.blade.php` — Recibo / Detalle

Diseño dual:
- **Modo normal**: detalle completo con datos de tienda, cabecera, items, totales, botones de acción.
- **Modo impresión** (`@media print`): formato de recibo limpio, oculta botones, sidebar, topbar.

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl">Venta #{{ $venta->id }}</h2>
                <p class="text-gray-600">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex gap-2 no-print">
                <button onclick="window.print()"
                        class="bg-brand-300 text-white px-4 py-2 rounded-lg">🖨️ Imprimir</button>
                <a href="{{ route('ventas.index') }}"
                   class="bg-gray-200 px-4 py-2 rounded-lg">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <!-- Recibo -->
        <div class="bg-white rounded-xl shadow-sm border p-8" id="recibo">
            <!-- Cabecera del recibo -->
            <div class="text-center border-b pb-6 mb-6">
                <h1 class="text-2xl font-bold">TiendaStock</h1>
                <p class="text-gray-500">Recibo de Venta #{{ $venta->id }}</p>
                <p class="text-gray-500">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <!-- Cliente (si aplica) -->
            @if($venta->cliente_nombre)
            <div class="mb-4">
                <p><strong>Cliente:</strong> {{ $venta->cliente_nombre }}</p>
            </div>
            @endif

            <!-- Items -->
            <table class="w-full mb-6">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2">Producto</th>
                        <th class="py-2 text-right">Precio</th>
                        <th class="py-2 text-right">Cant.</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->items as $item)
                    <tr class="border-b">
                        <td class="py-2">{{ $item->producto->nombre }}</td>
                        <td class="py-2 text-right">${{ number_format($item->precio_unitario, 2) }}</td>
                        <td class="py-2 text-right">{{ $item->cantidad }}</td>
                        <td class="py-2 text-right">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totales -->
            <div class="border-t pt-4 space-y-1 text-right">
                <p>Subtotal: ${{ number_format($venta->subtotal, 2) }}</p>
                @if($venta->descuento > 0)
                <p>Descuento: -${{ number_format($venta->descuento, 2) }}</p>
                @endif
                @if($venta->impuesto > 0)
                <p>Impuesto: +${{ number_format($venta->impuesto, 2) }}</p>
                @endif
                <p class="text-xl font-bold">Total: ${{ number_format($venta->total, 2) }}</p>
                <p>Pagó: ${{ number_format($venta->pago_con, 2) }}</p>
                <p>Cambio: ${{ number_format($venta->cambio, 2) }}</p>
            </div>

            <!-- Método de pago -->
            <div class="mt-4 pt-4 border-t">
                <p><strong>Método de pago:</strong> {{ ucfirst($venta->metodo_pago) }}</p>
                <p><strong>Procesado por:</strong> {{ $venta->user->name }}</p>
            </div>

            <!-- Estado -->
            <div class="mt-4 text-center">
                @if($venta->isAnulada())
                <span class="inline-block px-4 py-2 bg-red-100 text-red-700 rounded-full font-bold">
                    ANULADA
                </span>
                @endif
            </div>
        </div>

        <!-- Acciones post-recibo -->
        @if($venta->isCompletada())
        <div class="mt-6 text-center no-print">
            <form action="{{ route('ventas.cancel', $venta) }}" method="POST"
                  onsubmit="return confirm('¿Está seguro de anular esta venta?')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg font-bold">
                    Anular Venta
                </button>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    #recibo, #recibo * { visibility: visible; }
    #recibo { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
    .sidebar, .topbar, header { display: none !important; }
}
</style>
@endpush
```

### 5.5 UI States por vista

| Vista | Loading | Empty | Error | Success |
|-------|---------|-------|-------|---------|
| `ventas.pos` | Spinner en búsqueda (Alpine `searching`) | "El carrito está vacío" | Flash error: "Stock insuficiente..." | Flash + redirect a show |
| `ventas.index` | N/A (server-rendered) | "No hay ventas registradas" | N/A | Flash: "Venta anulada" |
| `ventas.show` | N/A | N/A (404 si no existe) | N/A | Flash: "Venta #N registrada" |
| `POST /ventas` | Botón deshabilitado "Procesando..." | Validation: "Debe agregar al menos un producto" | Validation errors + flash error | Redirect a show |

---

## 6. Route Design

### 6.1 Definiciones exactas

En `routes/web.php`, DENTRO del grupo `auth`:

```php
use App\Http\Controllers\VentaController;

// === AGREGAR DENTRO DE Route::middleware('auth')->group(function () { ===

Route::middleware('role:ADMIN,Ventas')->group(function () {
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/pos', [VentaController::class, 'create'])->name('ventas.pos');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/{venta}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
});
```

> **NOTA**: El orden importa. `GET /ventas/pos` DEBE definirse ANTES de `GET /ventas/{venta}` para que Laravel no intente resolver "pos" como un ID de venta.

### 6.2 Resumen de rutas

| Método | URI | Controller@action | Nombre | Middleware |
|--------|-----|-------------------|--------|------------|
| GET | `/ventas` | `VentaController@index` | `ventas.index` | auth, verified, role:ADMIN,Ventas |
| GET | `/ventas/pos` | `VentaController@create` | `ventas.pos` | auth, verified, role:ADMIN,Ventas |
| POST | `/ventas` | `VentaController@store` | `ventas.store` | auth, verified, role:ADMIN,Ventas |
| GET | `/ventas/{venta}` | `VentaController@show` | `ventas.show` | auth, verified, role:ADMIN,Ventas |
| POST | `/ventas/{venta}/cancel` | `VentaController@cancel` | `ventas.cancel` | auth, verified, role:ADMIN,Ventas |

### 6.3 Sidebar — Enlace condicional

En `sidebar.blade.php`, agregar DESPUÉS del enlace a Dashboard:

```blade
@if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))
<a href="{{ route('ventas.index') }}"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md
          {{ request()->routeIs('ventas.*') ? 'bg-slate-800 text-brand-300 border-l-2 border-brand-300' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}
          transition-all duration-200">
    <svg class="mr-3 h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    Ventas
</a>
@endif
```

> El SVG usado es un ícono de carrito/caja. Alternativa: usar heroicon `shopping-cart`.

### 6.4 Dashboard — Acciones rápidas para Ventas

En `dashboard.blade.php`, dentro de "Acciones Rápidas" y de la sección de roles:

```blade
@if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))
<a href="{{ route('ventas.pos') }}"
   class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-green-500 hover:shadow-md transition-all duration-200">
    <div class="h-10 w-10 bg-green-500 rounded-full flex items-center justify-center">
        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <div>
        <p class="text-sm font-semibold text-gray-900">Nueva Venta (POS)</p>
        <p class="text-xs text-gray-500">Abrir el punto de venta</p>
    </div>
</a>
@endif
```

---

## 7. Component Tree

```
routes/web.php
  └── Route::middleware(['auth', 'verified', 'role:ADMIN,Ventas'])
        ├── VentaController@index
        │     ├── App\Models\Venta (with: user, items)
        │     └── resources/views/ventas/index.blade.php
        │
        ├── VentaController@create
        │     └── resources/views/ventas/pos.blade.php
        │           └── Alpine.js (posApp)
        │                 ├── fetch → GET /productos/search (ProductoController@search)
        │                 └── fetch POST → /ventas (VentaController@store)
        │
        ├── VentaController@store
        │     ├── App\Http\Requests\StoreVentaRequest
        │     ├── DB::transaction
        │     │     ├── App\Models\Venta::create()
        │     │     ├── App\Models\Producto::lockForUpdate()
        │     │     ├── App\Models\VentaItem::create()
        │     │     └── Producto::decrement()
        │     └── redirect → ventas.show
        │
        ├── VentaController@show
        │     ├── App\Models\Venta (with: user, items.producto)
        │     └── resources/views/ventas/show.blade.php
        │           └── @media print styles
        │
        └── VentaController@cancel
              ├── App\Models\Venta
              ├── DB::transaction
              │     ├── foreach items
              │     │     └── Producto::increment()
              │     └── Venta::update(['estado' => 'anulada'])
              └── redirect → ventas.index
```

---

## 8. Database Decisions (ADR)

### ADR-01: `decimal(10,2)` para todos los campos monetarios

**Contexto**: Precios, totales, descuentos, impuestos, pago_con, cambio.  
**Decisión**: Usar `decimal(10, 2)` en lugar de `float` o `integer` (centavos).  
**Razón**: `float` introduce errores de redondeo ([IEEE 754](https://floating-point-gui.de/)). `integer` (centavos) es preciso pero requiere conversión manual en cada operación y es menos legible en debugging. `decimal(10,2)` es el tipo SQL estándar para dinero: preciso hasta 2 decimales, con 10 dígitos totales (hasta $99,999,999.99).  
**Tradeoff**: En SQLite, `decimal` se almacena como texto internamente pero Laravel lo maneja como `float` en PHP; la precisión la garantiza el binding de PDO. Si se migra a MySQL/PostgreSQL, `decimal` es el tipo nativo correcto.

### ADR-02: Copia de `precio_unitario` en `venta_items`

**Contexto**: El precio de venta de un producto puede cambiar después de la venta.  
**Decisión**: Almacenar `precio_unitario` como copia del valor de `productos.precio_venta` al momento de crear la venta.  
**Razón**: Cumple con el principio de inmutabilidad del registro financiero (NFR-02). Si el precio sube al día siguiente, la venta de ayer debe reflejar el precio que se cobró, no el actual.  
**Consecuencia**: Se duplica un valor que ya existe en `productos`, pero es deliberado y está documentado. La columna `subtotal` también se calcula y almacena para no recalcular.

### ADR-03: `ON DELETE CASCADE` en ambas FKs

**Contexto**: `venta_items.venta_id` → `ventas.id`, `venta_items.producto_id` → `productos.id`, `ventas.user_id` → `users.id`.  
**Decisión**: Usar `ON DELETE CASCADE` en las tres claves foráneas.  
**Razón**: Simplifica limpieza: si se elimina una venta (caso borde, ya que solo se anula), sus items se eliminan automáticamente. Si se elimina un producto (baja lógica recomendada pero no forzada), los items de venta que lo referencian no quedan huérfanos.  
**Riesgo mitigado**: Una venta NUNCA se elimina en operación normal (solo se anula, cambiando `estado`). `CASCADE` es para consistencia en mantenimiento de base de datos o migraciones.

### ADR-04: Estado como string `varchar(20)`, no boolean

**Contexto**: `ventas.estado` puede ser `completada` o `anulada`.  
**Decisión**: Usar `string(20)` con valores textuales en lugar de `boolean` (`activa=1/0`) o `enum`.  
**Razón**: Los strings son auto-documentados en la base de datos y en debugging. Usar `enum` nativo de SQL sería preferible pero SQLite no lo soporta, y el `string` permite extender a más estados en el futuro (ej. `pendiente`, `reembolsada`).  
**Tradeoff**: Sin constraint a nivel DB. Se valida en el controlador y en el Form Request.

### ADR-05: `lockForUpdate` incluso en SQLite

**Contexto**: Proteger contra race conditions al deducir stock.  
**Decisión**: Usar `Producto::lockForUpdate()` dentro de la transacción.  
**Razón**: Aunque SQLite serializa escrituras, `lockForUpdate()` añade claridad semántica al código y es necesario si se migra a PostgreSQL/MySQL en el futuro. No tiene costo adicional significativo.

### ADR-06: Carrito client-side (Alpine.js), no en sesión

**Contexto**: El carrito de compras del POS.  
**Decisión**: El estado del carrito vive exclusivamente en el navegador mediante Alpine.js. No se persiste en sesión ni en base de datos.  
**Razón**: Simplifica drásticamente la implementación: no hay endpoints para agregar/quitar items, no hay limpieza de carritos abandonados, no hay estado compartido entre pestañas. Si el usuario cierra el navegador, el carrito se pierde — comportamiento documentado y aceptado.  
**Tradeoff**: El usuario no puede recuperar un carrito tras cerrar la página. Aceptable para MVP.

### ADR-07: Form Request dedicado, no validación inline

**Contexto**: Validación de `POST /ventas`.  
**Decisión**: Crear `StoreVentaRequest` separado con reglas, mensajes y `withValidator` para validaciones complejas.  
**Razón**: Sigue el principio de Single Responsibility y el patrón existente del proyecto. Mantiene el controlador limpio y las validaciones reutilizables. La validación de stock se hace en dos capas: (1) en el `withValidator` del Form Request como cortocircuito rápido, y (2) dentro de la transacción con `lockForUpdate` como defensa definitiva.

---

## 9. Testing Strategy

### 9.1 Configuración

- Framework: PHPUnit (ya configurado)
- Base de datos: SQLite in-memory (`RefreshDatabase` trait)
- Factory states existentes: `admin()`, `ventas()`, `controlStock()`
- Nuevas factories: `VentaFactory`, `VentaItemFactory`

### 9.2 `VentaFactory`

```php
class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'subtotal'      => 100,
            'descuento'     => 0,
            'impuesto'      => 0,
            'total'         => 100,
            'pago_con'      => 100,
            'cambio'        => 0,
            'metodo_pago'   => 'efectivo',
            'estado'        => 'completada',
        ];
    }

    public function anulada(): static
    {
        return $this->state(fn (array $a) => ['estado' => 'anulada']);
    }
}
```

### 9.3 `VentaItemFactory`

```php
class VentaItemFactory extends Factory
{
    protected $model = VentaItem::class;

    public function definition(): array
    {
        return [
            'venta_id'        => Venta::factory(),
            'producto_id'     => Producto::factory(),
            'cantidad'        => 1,
            'precio_unitario' => 100,
            'subtotal'        => 100,
        ];
    }
}
```

> **Nota**: Como `ProductoFactory` no existe aún, se DEBE crear o usar `Producto::create([...])` directamente en los tests.

### 9.4 Escenarios de Test

| # | Test | Archivo | Flujo |
|---|------|---------|-------|
| 1 | `ventas_page_loads_for_ventas_role` | `VentaControllerTest` | GET /ventas como Ventas → 200 |
| 2 | `ventas_page_loads_for_admin_role` | `VentaControllerTest` | GET /ventas como Admin → 200 |
| 3 | `ventas_page_forbidden_for_control_stock` | `VentaControllerTest` | GET /ventas como ControlStock → 403 |
| 4 | `pos_page_loads` | `VentaControllerTest` | GET /ventas/pos → 200 |
| 5 | `can_create_sale_with_valid_data` | `VentaControllerTest` | POST /ventas con items válidos → redirect ventas.show, stock decrementado, DB tiene Venta + VentaItems |
| 6 | `cannot_create_sale_with_empty_cart` | `VentaControllerTest` | POST /ventas sin items → validation error |
| 7 | `cannot_create_sale_with_insufficient_stock` | `VentaControllerTest` | POST /ventas con cantidad > stock → validation error + stock unchanged |
| 8 | `cannot_create_sale_with_pago_menor_a_total` | `VentaControllerTest` | POST /ventas con pago_con < total → validation error |
| 9 | `can_view_sale_detail` | `VentaControllerTest` | GET /ventas/{venta} → 200, see items |
| 10 | `can_cancel_completed_sale` | `VentaCancelTest` | POST /ventas/{venta}/cancel → estado=anulada, stock restored |
| 11 | `cannot_cancel_already_cancelled_sale` | `VentaCancelTest` | POST /ventas/{venta}/cancel en venta ya anulada → error |
| 12 | `cannot_cancel_sale_as_control_stock` | `VentaCancelTest` | POST /ventas/{venta}/cancel como ControlStock → 403 |
| 13 | `ventas_link_hidden_in_sidebar_for_control_stock` | `SidebarVisibilityTest` | Dashboard como ControlStock → no see "Ventas" |
| 14 | `ventas_link_shown_in_sidebar_for_ventas` | `SidebarVisibilityTest` | Dashboard como Ventas → see "Ventas" |
| 15 | `guest_redirected_from_ventas_routes` | `VentaControllerTest` | GET /ventas sin auth → redirect /login |
| 16 | `store_creates_items_with_correct_prices` | `VentaControllerTest` | POST /ventas → verificar `precio_unitario` = `producto.precio_venta` al momento |
| 17 | `store_uses_transaction_rollback_on_failure` | `VentaControllerTest` | POST /ventas con items mixtos (uno válido, otro sin stock) → rollback, no hay Venta creada |

### 9.5 Test de ejemplo (pseudocódigo del más complejo)

```php
public function test_can_create_sale_with_valid_data(): void
{
    $user = User::factory()->ventas()->create();
    $producto = Producto::factory()->create([
        'precio_venta' => 25.00,
        'cantidad'     => 10,
    ]);

    $response = $this->actingAs($user)->post('/ventas', [
        'items' => [
            ['producto_id' => $producto->id, 'cantidad' => 3],
        ],
        'subtotal'    => 75.00,
        'total'       => 75.00,
        'pago_con'    => 100.00,
        'metodo_pago' => 'efectivo',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('ventas', [
        'user_id' => $user->id,
        'total'   => 75.00,
        'estado'  => 'completada',
    ]);

    $this->assertDatabaseHas('venta_items', [
        'producto_id'     => $producto->id,
        'cantidad'        => 3,
        'precio_unitario' => 25.00,
        'subtotal'        => 75.00,
    ]);

    $producto->refresh();
    $this->assertEquals(7, $producto->cantidad);
}
```

---

## 10. File Manifest

### Archivos a crear (8)

| # | Ruta | Propósito |
|---|------|-----------|
| 1 | `src/database/migrations/YYYY_MM_DD_HHMMSS_create_ventas_table.php` | Migración tabla `ventas` |
| 2 | `src/database/migrations/YYYY_MM_DD_HHMMSS_create_venta_items_table.php` | Migración tabla `venta_items` |
| 3 | `src/app/Models/Venta.php` | Modelo Eloquent `Venta` |
| 4 | `src/app/Models/VentaItem.php` | Modelo Eloquent `VentaItem` |
| 5 | `src/app/Http/Requests/StoreVentaRequest.php` | Form Request para validación de venta |
| 6 | `src/app/Http/Controllers/VentaController.php` | Controlador con index, create, store, show, cancel |
| 7 | `src/resources/views/ventas/index.blade.php` | Vista de historial con tabla y filtros |
| 8 | `src/resources/views/ventas/pos.blade.php` | Vista POS con Alpine.js carrito |
| 9 | `src/resources/views/ventas/show.blade.php` | Vista detalle/recibo con @media print |
| 10 | `src/database/factories/VentaFactory.php` | Factory para Venta |
| 11 | `src/database/factories/VentaItemFactory.php` | Factory para VentaItem |
| 12 | `src/tests/Feature/VentaControllerTest.php` | Feature tests para el módulo de ventas |
| 13 | `src/tests/Feature/VentaCancelTest.php` | Feature tests para anulación (o dentro del mismo archivo) |

### Archivos a modificar (5)

| # | Ruta | Cambio |
|---|------|--------|
| 1 | `src/app/Models/Producto.php` | Agregar `use HasMany`, relación `ventaItems()` |
| 2 | `src/app/Models/User.php` | (Opcional) Agregar relación `ventas()` |
| 3 | `src/routes/web.php` | Agregar grupo de rutas para Ventas dentro del middleware auth |
| 4 | `src/resources/views/layouts/sidebar.blade.php` | Agregar enlace "Ventas" condicional para ADMIN/Ventas |
| 5 | `src/resources/views/dashboard.blade.php` | (Opcional) Agregar acción rápida "Nueva Venta" para ADMIN/Ventas |

### Orden de implementación

```
FASE 1 — Infraestructura
  1. Migraciones (crear ambas tablas)
  2. Models (Venta, VentaItem)
  3. Modificar Producto (relación ventaItems)
  4. Modificar User (relación ventas — opcional)

FASE 2 — Backend
  5. StoreVentaRequest (Form Request)
  6. VentaController (todos los métodos)
  7. Rutas en web.php

FASE 3 — Frontend
  8. ventas/index.blade.php
  9. ventas/pos.blade.php (con Alpine.js)
  10. ventas/show.blade.php

FASE 4 — Navegación
  11. Sidebar (enlace Ventas)
  12. Dashboard (acción rápida — opcional)

FASE 5 — Tests
  13. VentaFactory + VentaItemFactory
  14. Feature tests (VentaControllerTest)
```

---

## 11. Seguridad y Consideraciones Adicionales

### 11.1 CSRF

Todas las rutas POST (`store`, `cancel`) están protegidas por CSRF automáticamente. La petición desde Alpine.js debe incluir el token:

```javascript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
```

### 11.2 SQL Injection

No hay riesgo: todas las consultas usan Eloquent ORM con parameter binding.

### 11.3 Mass Assignment

Los modelos usan `$fillable` explícito. No hay campos `$guarded` que expongan atributos no deseados.

### 11.4 Rate Limiting

Para producción, se recomienda aplicar rate limiting en `POST /ventas`:

```php
Route::middleware(['auth', 'verified', 'role:ADMIN,Ventas', 'throttle:10,1'])->group(function () {
    // rutas de ventas
});
```

### 11.5 Integridad de precios

El `precio_unitario` se copia del producto en el momento de la transacción. Si el precio del producto cambia MINUTOS después de crear la venta, la venta ya registrada conserva el precio original. Esto es deliberado y cumple con NFR-02.

---

## 12. Limitaciones Conocidas

1. **Sin carrito persistente**: Si el usuario recarga la página POS, el carrito se pierde. No hay confirmación "¿Perderás el carrito?".
2. **Una sola caja**: SQLite no maneja concurrencia de escritura a nivel de múltiples cajeros. Si se necesita multi-caja en el futuro, migrar a PostgreSQL/MySQL.
3. **Stock manual**: Si un ADMIN modifica `productos.cantidad` manualmente entre una venta y su anulación, la restauración de stock al anular puede dar un valor inesperado (el `increment()` suma sin preguntar).
4. **Sin historial de precios**: No se registra el precio histórico del producto. La única fuente de verdad del precio al momento de la venta es `venta_items.precio_unitario`.
