<?php

namespace App\Http\Requests;

use App\Models\Venta;
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
            'cart_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1|max:2147483647',

            'cliente_nombre' => 'nullable|string|max:255',
            'descuento' => ['nullable', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/'],
            'pago_con' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/'],
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia',
            'tipo_entrega' => ['required', 'string', 'in:'.implode(',', Venta::TIPOS_ENTREGA)],
        ];
    }

    public function messages(): array
    {
        return [
            'cart_id.required' => 'Agrega los productos al carrito antes de registrar la venta.',
            'cart_id.integer' => 'El carrito no es válido.',
            'descuento.regex' => 'Ingresa un descuento válido con hasta dos decimales.',
            'pago_con.regex' => 'Ingresa un monto válido con hasta dos decimales.',
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
            'tipo_entrega.required' => 'Debe indicar si la venta fue en el local o envío por Uber.',
            'tipo_entrega.in' => 'Tipo de entrega inválido. Use "local" o "uber".',
        ];
    }

}
