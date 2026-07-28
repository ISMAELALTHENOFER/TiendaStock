<?php

namespace App\Models;

use Database\Factories\VentaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    /** @use HasFactory<VentaFactory> */
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'cliente_nombre',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'pago_con',
        'cambio',
        'metodo_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
            'pago_con' => 'decimal:2',
            'cambio' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }

    public function isCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    public function isAnulada(): bool
    {
        return $this->estado === 'anulada';
    }
}
