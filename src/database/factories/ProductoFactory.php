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
            'imagen' => null,
            'activo' => true,
        ];
    }

    /**
     * Product without a talle (e.g. accessories with single size).
     */
    public function sinTalle(): static
    {
        return $this->state(fn (array $attributes) => [
            'talle' => null,
        ]);
    }

    /**
     * Product without a color (e.g. non-textile items).
     */
    public function sinColor(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => null,
        ]);
    }

    /**
     * Product with a persisted relative image path under the public disk.
     */
    public function conImagen(): static
    {
        return $this->state(fn (array $attributes) => [
            'imagen' => 'productos/'.fake()->uuid().'.jpg',
        ]);
    }

    /**
     * Soft-disabled product (activo = false).
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
