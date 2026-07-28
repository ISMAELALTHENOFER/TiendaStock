<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word().' '.fake()->randomElement(['M', 'L', 'S', 'XL']),
            'descripcion' => fake()->sentence(),
            'categoria_id' => Categoria::factory(),
            'precio_compra' => fake()->randomFloat(2, 5, 50),
            'precio_venta' => fake()->randomFloat(2, 10, 100),
            'cantidad' => fake()->numberBetween(1, 100),
            'talle' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->safeColorName(),
            'activo' => true,
        ];
    }
}
