<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subtotal' => 100.00,
            'descuento' => 0,
            'impuesto' => 0,
            'total' => 100.00,
            'pago_con' => 100.00,
            'cambio' => 0,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => Venta::TIPO_ENTREGA_LOCAL,
            'estado' => 'completada',
        ];
    }

    public function anulada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'anulada',
        ]);
    }
}
