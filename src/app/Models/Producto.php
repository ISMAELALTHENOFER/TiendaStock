<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

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
        'activo',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ventaItems(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }

    // Ganancia calculada automáticamente
    public function getGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }
}
