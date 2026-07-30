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
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ventaItems(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }

    /**
     * Zero-stock invariant (single chokepoint): a product with cantidad===0
     * can never be active. Forced on BOTH create and update (and any future
     * code path that persists the model), so the POS/search/store always see
     * a coherent activo state regardless of who flipped the flag.
     *
     * The rule is intentionally one-directional: it forces activo=false when
     * stock hits 0, but never forces activo=true when stock is positive —
     * admins may keep an in-stock product manually deactivated.
     */
    protected static function booted()
    {
        static::saving(function (Producto $producto) {
            if ((int) $producto->cantidad === 0) {
                $producto->activo = false;
            }
        });
    }

    // Ganancia calculada automáticamente
    public function getGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }
}
