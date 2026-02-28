<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'precio_compra',
        'precio_venta',
        'cantidad',
        'talle',
        'color',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Ganancia calculada automáticamente
    public function getGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }
}
