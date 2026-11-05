<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityRecorder;
use App\Services\SaleCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    /**
     * Listado de ventas con filtros.
     * GET /ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->expectsJson()) {
            $query->with('items');
        } else {
            $query->withCount('items');
        }

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('buscar')) {
            $search = $request->input('buscar');
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('cliente_nombre', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $perPage = in_array((string) $request->input('per_page'), ['15', '30', '50'], true)
            ? (int) $request->input('per_page')
            : 15;
        $ventas = $query->paginate($perPage)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($ventas);
        }

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

        $venta = DB::transaction(function () use ($validated, $request) {
            $cart = DB::table('sale_carts')->where('id', $validated['cart_id'])
                ->where('user_id', $request->user()->id)->whereNull('consumed_at')->lockForUpdate()->first();
            if (! $cart) {
                throw ValidationException::withMessages(['cart_id' => 'El carrito ya no está disponible.']);
            }

            $prices = DB::table('sale_cart_items')->where('sale_cart_id', $cart->id)->get()->keyBy('producto_id');
            $ids = collect($validated['items'])->pluck('producto_id')->unique()->sort()->values();
            $products = Producto::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $quantities = [];
            $subtotal = 0;
            foreach ($validated['items'] as $index => $item) {
                $id = $item['producto_id'];
                $product = $products->get($id);
                $price = $prices->get($id);
                if (! $product || ! $price || ! $product->activo) {
                    throw ValidationException::withMessages(["items.$index.producto_id" => 'El producto no está disponible en el carrito.']);
                }
                $quantities[$id] = ($quantities[$id] ?? 0) + $item['cantidad'];
                if ($quantities[$id] > $product->cantidad) {
                    throw ValidationException::withMessages(["items.$index.cantidad" => "Stock insuficiente para {$product->nombre}. Disponible: {$product->cantidad}."]);
                }
                if ($price->price_cents > intdiv(9999999999 - $subtotal, $item['cantidad'])) {
                    throw ValidationException::withMessages(['items' => 'El importe de la venta supera el máximo permitido.']);
                }
                $subtotal += $price->price_cents * $item['cantidad'];
            }
            $discount = SaleCartService::cents((string) ($validated['descuento'] ?? '0'));
            $paid = SaleCartService::cents((string) $validated['pago_con']);
            $total = $subtotal - $discount;
            if ($discount * 2 > $subtotal || $total <= 0) {
                throw ValidationException::withMessages(['descuento' => 'El descuento no puede superar el 50 % del subtotal.']);
            }
            if ($paid < $total) {
                throw ValidationException::withMessages(['pago_con' => 'El monto recibido debe ser mayor o igual al total de la venta.']);
            }
            $venta = Venta::create([
                'user_id' => $request->user()->id,
                'cliente_nombre' => $validated['cliente_nombre'] ?? null,
                'subtotal' => SaleCartService::amount($subtotal),
                'descuento' => SaleCartService::amount($discount),
                'impuesto' => '0.00',
                'total' => SaleCartService::amount($total),
                'pago_con' => SaleCartService::amount($paid),
                'cambio' => SaleCartService::amount($paid - $total),
                'metodo_pago' => $validated['metodo_pago'],
                'tipo_entrega' => $validated['tipo_entrega'],
                'estado' => 'completada',
            ]);
            foreach ($validated['items'] as $item) {
                $price = $prices->get($item['producto_id'])->price_cents;
                $venta->items()->create([
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => SaleCartService::amount($price),
                    'subtotal' => SaleCartService::amount($price * $item['cantidad']),
                ]);
            }
            foreach ($quantities as $id => $quantity) {
                $product = $products->get($id);
                $product->cantidad -= $quantity;
                $product->save();
            }
            DB::table('sale_carts')->where('id', $cart->id)->update(['consumed_at' => now(), 'updated_at' => now()]);

            return $venta;
        });

        ActivityRecorder::recordAfterCommit(
            $request->user(), 'sale.created', 'Sale created', "Sale #{$venta->id} was created.", $venta
        );

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('ventas.show', $venta)]);
        }

        return redirect()->route('ventas.show', $venta)
            ->with('success', "Venta #{$venta->id} registrada correctamente.");
    }

    /**
     * Mostrar detalle de venta (recibo).
     * GET /ventas/{venta}
     */
    public function show(Venta $venta)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || ($user->isVentas() && $venta->user_id === $user->id), 403);

        $venta->load('user', 'items.producto');

        return view('ventas.show', compact('venta'));
    }

    /**
     * Anular una venta y restaurar stock.
     * POST /ventas/{venta}/cancel
     */
    public function cancel(Venta $venta)
    {
        $error = DB::transaction(function () use ($venta) {
            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $user = auth()->user();
            abort_unless($user->isAdmin() || ($user->isVentas() && $venta->user_id === $user->id), 403);

            if (! $venta->isCompletada()) {
                return $venta->isAnulada()
                    ? 'Esta venta ya fue anulada anteriormente.'
                    : 'Solo se pueden anular ventas completadas.';
            }

            $items = $venta->items()->get()->groupBy('producto_id')->sortKeys();
            foreach ($items as $productId => $productItems) {
                $product = Producto::whereKey($productId)->lockForUpdate()->firstOrFail();
                $product->cantidad += $productItems->sum('cantidad');
                $product->activo = true;
                $product->save();
            }

            $venta->update(['estado' => 'anulada']);
        });

        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        ActivityRecorder::recordAfterCommit(
            auth()->user(), 'sale.cancelled', 'Sale cancelled', "Sale #{$venta->id} was cancelled.", $venta
        );

        return redirect()->route('ventas.index')
            ->with('success', "Venta #{$venta->id} anulada. Stock restaurado.");
    }
}
