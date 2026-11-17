<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    // Purchase cost is ADMIN-only: hidden from every serialization unless revealCostTo() opts in.
    protected $hidden = ['precio_compra'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
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

    public function imagenRecord(): HasOne
    {
        return $this->hasOne(ProductoImagen::class);
    }

    /** The only place that grants cost visibility: ADMIN sees precio_compra. */
    public function revealCostTo(?User $user): static
    {
        return $user?->isAdmin() ? $this->makeVisible('precio_compra')->append('ganancia') : $this;
    }

    /** Stock rule: a product is active exactly when it has stock. Call it wherever cantidad changes. */
    public function syncActivoWithStock(): static
    {
        $this->activo = (int) $this->cantidad > 0;

        return $this;
    }

    // Ganancia calculada automáticamente
    public function getGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }
}
