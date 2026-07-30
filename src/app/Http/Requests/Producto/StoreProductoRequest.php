<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    use ProductoRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<string>>
     */
    public function rules(): array
    {
        return $this->productoRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->productoMessages();
    }
}
