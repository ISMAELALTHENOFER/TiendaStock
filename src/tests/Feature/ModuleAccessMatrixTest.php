<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_control_stock_is_forbidden_from_sales_dashboard_and_admin(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 10]);
        $venta = Venta::factory()->create();
        $ventas = Venta::count();
        $this->actingAs(User::factory()->controlStock()->create());

        $item = ['producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 1];
        foreach ([
            ['post', '/ventas/cart', ['cart_id' => 1, 'items' => [$item]]],
            ['delete', '/ventas/cart/1/items', []],
            ['post', '/ventas', ['items' => [$item]]],
            ['post', "/ventas/{$venta->id}/cancel", []],
            ['get', "/ventas/{$venta->id}", []],
            ['get', '/dashboard', []],
            ['get', '/dashboard/analytics', []],
            ['get', '/admin/users', []],
        ] as [$method, $uri, $data]) {
            $this->json($method, $uri, $data)->assertForbidden();
        }

        $this->assertSame($ventas, Venta::count());
        $this->assertSame(10, (int) $producto->fresh()->cantidad);
        $this->assertSame('completada', $venta->fresh()->estado);
    }

    public function test_ventas_is_forbidden_from_catalog_dashboard_and_admin(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 5, 'activo' => false]);
        $categorias = Categoria::count();
        $productos = Producto::count();
        $this->actingAs(User::factory()->ventas()->create());

        foreach ([
            ['post', '/categorias/inline', ['nombre' => 'Nueva']],
            ['get', '/productos/check-duplicate?nombre=x', []],
            ['patch', "/productos/{$producto->id}/activate", []],
            ['get', '/productos/data', []],
            ['post', '/productos', ['nombre' => 'Nuevo', 'precio' => 1, 'cantidad' => 1]],
            ['get', '/categorias', []],
            ['get', '/dashboard', []],
            ['get', '/admin/users', []],
        ] as [$method, $uri, $data]) {
            $this->json($method, $uri, $data)->assertForbidden();
        }

        $this->assertSame($categorias, Categoria::count());
        $this->assertSame($productos, Producto::count());
        $this->assertFalse((bool) $producto->fresh()->activo);
    }

    public function test_admin_reaches_every_module(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        foreach (['dashboard', 'productos.index', 'categorias.index', 'ventas.index', 'ventas.pos', 'admin.users.index'] as $name) {
            $this->assertNotSame(403, $this->get(route($name))->status(), $name);
        }
    }
}
