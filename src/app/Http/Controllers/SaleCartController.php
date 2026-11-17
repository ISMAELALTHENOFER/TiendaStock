<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\SaleCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleCartController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cart_id' => ['nullable', 'integer'],
            'producto_id' => ['required', 'integer'],
            'precio_venta' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            if (isset($data['cart_id'])) {
                $cart = $this->lockCart($request, $data['cart_id']);
                $id = $cart->id;
            } else {
                $id = DB::table('sale_carts')->insertGetId(['user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            }

            $product = Producto::whereKey($data['producto_id'])->lockForUpdate()->first();
            if (! $product || ! $product->activo || $product->cantidad < 1) {
                throw ValidationException::withMessages(['producto_id' => 'El producto no está disponible.']);
            }
            $item = DB::table('sale_cart_items')->where('sale_cart_id', $id)->where('producto_id', $product->id)->first();
            if (! $item) {
                $price = SaleCartService::cents($product->precio_venta);
                if ($price !== SaleCartService::cents($data['precio_venta'])) {
                    throw ValidationException::withMessages(['precio_venta' => 'El precio cambió. Busca el producto nuevamente.']);
                }
                DB::table('sale_cart_items')->insert(['sale_cart_id' => $id, 'producto_id' => $product->id, 'price_cents' => $price]);
            } else {
                $price = (int) $item->price_cents;
                if ($price !== SaleCartService::cents($data['precio_venta'])) {
                    throw ValidationException::withMessages(['precio_venta' => 'El precio del carrito cambió. Vacía el carrito para iniciar uno nuevo.']);
                }
            }

            return response()->json(['id' => $id, 'precio_venta' => SaleCartService::amount($price)]);
        });
    }

    public function destroy(Request $request, int $cartId, ?int $productId = null)
    {
        return DB::transaction(function () use ($request, $cartId, $productId) {
            $cart = $this->lockCart($request, $cartId);
            $items = DB::table('sale_cart_items')->where('sale_cart_id', $cart->id);
            if ($productId !== null) {
                $items->where('producto_id', $productId);
            }
            $items->delete();

            return response()->json(['removed' => true]);
        });
    }

    private function lockCart(Request $request, int $cartId): object
    {
        $cart = DB::table('sale_carts')->where('id', $cartId)
            ->where('user_id', $request->user()->id)->whereNull('consumed_at')->lockForUpdate()->first();
        if (! $cart) {
            throw ValidationException::withMessages(['cart_id' => 'El carrito ya no está disponible.']);
        }

        return $cart;
    }
}
