<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Models\ProductoImagen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportProductImages extends Command
{
    protected $signature = 'productos:import-images';

    protected $description = 'Copy referenced product images from the public disk into the database without deleting source files';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $imported = 0;

        Producto::query()->whereNotNull('imagen')->orderBy('id')->each(function (Producto $producto) use ($disk, &$imported): void {
            if (! str_starts_with($producto->imagen, 'productos/')
                || str_contains($producto->imagen, '../')
                || str_contains($producto->imagen, '..\\')
                || ProductoImagen::where('producto_id', $producto->id)->exists()
                || ! $disk->exists($producto->imagen)
                || $disk->size($producto->imagen) > 2048 * 1024) {
                return;
            }

            $bytes = $disk->get($producto->imagen);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($bytes);
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $this->warn("Skipped unsupported image for product {$producto->id}.");
                return;
            }

            $dimensions = function_exists('getimagesizefromstring') ? @getimagesizefromstring($bytes) : false;
            ProductoImagen::create([
                'producto_id' => $producto->id,
                'bytes' => $bytes,
                'mime' => $mime,
                'width' => $dimensions ? $dimensions[0] : null,
                'height' => $dimensions ? $dimensions[1] : null,
                'sha1' => sha1($bytes),
            ]);
            $imported++;
        });

        $this->info("Imported {$imported} product image(s). Source files were left unchanged.");

        return self::SUCCESS;
    }
}
