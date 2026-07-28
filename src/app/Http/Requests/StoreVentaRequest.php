<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',

            'cliente_nombre' => 'nullable|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'impuesto' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'pago_con' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto al carrito.',
            'items.min' => 'Debe agregar al menos un producto al carrito.',
            'items.*.producto_id.required' => 'Cada ítem debe tener un producto.',
            'items.*.producto_id.exists' => 'Uno de los productos no existe en el sistema.',
            'items.*.cantidad.required' => 'Cada ítem debe tener una cantidad.',
            'items.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'total.required' => 'El total es obligatorio.',
            'total.min' => 'El total debe ser mayor a cero.',
            'pago_con.required' => 'Debe ingresar el monto recibido.',
            'pago_con.min' => 'El monto recibido debe ser mayor a cero.',
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'Método de pago inválido. Use efectivo, tarjeta o transferencia.',
        ];
    }

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
                    $producto = Producto::find($item['producto_id']);
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
