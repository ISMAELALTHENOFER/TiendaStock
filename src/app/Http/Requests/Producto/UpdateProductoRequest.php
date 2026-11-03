<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
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
        return $this->productoRules() + ['stock_original' => ['required', 'integer', 'min:0']];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->productoMessages();
    }
}
