<?php

namespace App\Http\Requests\Producto;

/**
 * Shared validation rules for product create/update forms.
 *
 * Implemented as a trait so StoreProductoRequest and UpdateProductoRequest
 * stay in sync without duplicating the ruleset. Validation lives in a Form
 * Request (not inline) to replace $request->all() mass-assignment and to
 * keep `activo` out of validated data.
 */
trait ProductoRules
{
    /**
     * Shared Spanish validation messages for product fields.
     *
     * @return array<string, string>
     */
    public function productoMessages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'precio_compra.required' => 'El costo es obligatorio.',
            'precio_compra.numeric' => 'El costo debe ser un valor numérico.',
            'precio_compra.min' => 'El costo no puede ser negativo.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe ser un valor numérico.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'talle.max' => 'El talle no puede superar los 20 caracteres.',
            'color.max' => 'El color no puede superar los 50 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
            'imagen.max' => 'La imagen no puede superar los 2048 KB.',
        ];
    }

    /**
     * Shared validation rules for product store/update.
     *
     * @return array<string, string|array<string>>
     */
    public function productoRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'talle' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
