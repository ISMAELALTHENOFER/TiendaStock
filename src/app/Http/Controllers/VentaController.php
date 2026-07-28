<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Producto;
use App\Models\Venta;
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

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

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

        try {
            $venta = DB::transaction(function () use ($validated) {
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'cliente_nombre' => $validated['cliente_nombre'] ?? null,
                    'subtotal' => $validated['subtotal'],
                    'descuento' => $validated['descuento'] ?? 0,
                    'impuesto' => $validated['impuesto'] ?? 0,
                    'total' => $validated['total'],
                    'pago_con' => $validated['pago_con'],
                    'cambio' => $validated['pago_con'] - $validated['total'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'completada',
                ]);

                foreach ($validated['items'] as $item) {
                    $producto = Producto::where('id', $item['producto_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($item['cantidad'] > $producto->cantidad) {
                        throw new \RuntimeException(
                            "Stock insuficiente para {$producto->nombre}. ".
                            "Disponible: {$producto->cantidad}, solicitado: {$item['cantidad']}"
                        );
                    }

                    $venta->items()->create([
                        'producto_id' => $producto->id,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $producto->precio_venta,
                        'subtotal' => $item['cantidad'] * $producto->precio_venta,
                    ]);

                    $producto->decrement('cantidad', $item['cantidad']);
                }

                return $venta;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('ventas.create')
                ->with('error', $e->getMessage());
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
            foreach ($venta->items as $item) {
                Producto::where('id', $item->producto_id)
                    ->lockForUpdate()
                    ->increment('cantidad', $item->cantidad);
            }

            $venta->update(['estado' => 'anulada']);
        });

        return redirect()->route('ventas.index')
            ->with('success', "Venta #{$venta->id} anulada. Stock restaurado.");
    }
}
