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

    public const TIPO_ENTREGA_LOCAL = 'local';

    public const TIPO_ENTREGA_UBER = 'uber';

    /** @var list<string> */
    public const TIPOS_ENTREGA = [
        self::TIPO_ENTREGA_LOCAL,
        self::TIPO_ENTREGA_UBER,
    ];

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
        'tipo_entrega',
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

    public function isLocal(): bool
    {
        return $this->tipo_entrega === self::TIPO_ENTREGA_LOCAL;
    }

    public function isUber(): bool
    {
        return $this->tipo_entrega === self::TIPO_ENTREGA_UBER;
    }

    /**
     * Etiqueta legible para mostrar en vistas/reporte.
     */
    public function etiquetaTipoEntrega(): string
    {
        return match ($this->tipo_entrega) {
            self::TIPO_ENTREGA_LOCAL => 'En el local',
            self::TIPO_ENTREGA_UBER => 'Envío por Uber',
            default => 'No especificado',
        };
    }
}
