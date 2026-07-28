<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VentaItem>
 */
class VentaItemFactory extends Factory
{
    protected $model = VentaItem::class;

    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => 1,
            'precio_unitario' => 100.00,
            'subtotal' => fn (array $attributes) => $attributes['cantidad'] * $attributes['precio_unitario'],
        ];
    }
}
