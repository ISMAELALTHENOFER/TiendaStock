<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $fillable = ['producto_id', 'bytes', 'mime', 'width', 'height', 'sha1'];

    protected $hidden = ['bytes'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
