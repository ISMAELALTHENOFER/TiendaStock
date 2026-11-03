<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductoImagenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['frontend.routes.productos.show' => 'blade']);

        $this->admin = User::factory()->admin()->create();
        $this->categoria = Categoria::factory()->create();
    }

    /**
     * Builds a real PNG UploadedFile without requiring the GD extension.
     *
     * `UploadedFile::fake()->image()` needs GD to render pixels. The
     * `image|mimes` validation rule only inspects the file's magic bytes via
     * finfo (`guessExtension()`), so a minimal valid PNG (optionally padded
     * with trailing bytes) satisfies both the rule and the size check.
     */
    private function pngImage(int $kilobytes = 1, string $name = 'foto.png'): UploadedFile
    {
        // Minimal valid 1x1 transparent PNG.
        $base = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true
        );

        $targetBytes = $kilobytes * 1024;
        if (strlen($base) < $targetBytes) {
            $base = str_pad($base, $targetBytes, "\0");
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'imgtest_');
        file_put_contents($tmpPath, $base);

        return new UploadedFile($tmpPath, $name, 'image/png', null, true);
    }

    public function test_valid_image_upload_stores_bytes_in_database_not_public_disk(): void
    {
        $file = $this->pngImage(1, 'remera.png');

        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Remera con foto',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.00,
            'precio_venta' => 25.00,
            'cantidad' => 5,
            'talle' => 'M',
            'color' => 'Rojo',
            'imagen' => $file,
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto = Producto::where('nombre', 'Remera con foto')->first();
        $this->assertNotNull($producto);
        $this->assertNotNull($producto->imagen);
        $this->assertSame('database/'.$producto->id, $producto->imagen);
        $image = ProductoImagen::where('producto_id', $producto->id)->firstOrFail();
        $this->assertSame($file->get(), $image->bytes);
        $this->assertSame('image/png', $image->mime);
        $this->assertSame(sha1($file->get()), $image->sha1);
        $this->assertSame([], Storage::disk('public')->files('productos'));
    }

    public function test_invalid_mime_rejected_with_422_and_no_file_persisted(): void
    {
        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto con PDF',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.00,
            'precio_venta' => 25.00,
            'cantidad' => 5,
            'talle' => 'M',
            'color' => 'Rojo',
            'imagen' => $file,
        ]);

        $response->assertSessionHasErrors('imagen');
        $this->assertDatabaseMissing('productos', ['nombre' => 'Producto con PDF']);

        // No file should have been written.
        $files = Storage::disk('public')->files('productos');
        $this->assertEmpty($files, 'No image should be persisted when validation fails.');
    }

    public function test_image_over_2048_kilobytes_rejected_with_422(): void
    {
        $file = $this->pngImage(2049, 'enorme.png');

        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto con imagen enorme',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.00,
            'precio_venta' => 25.00,
            'cantidad' => 5,
            'talle' => 'M',
            'color' => 'Rojo',
            'imagen' => $file,
        ]);

        $response->assertSessionHasErrors('imagen');
        $this->assertDatabaseMissing('productos', ['nombre' => 'Producto con imagen enorme']);
    }

    public function test_store_without_image_creates_product_with_null_imagen(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto sin foto',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.00,
            'precio_venta' => 25.00,
            'cantidad' => 5,
            'talle' => 'M',
            'color' => 'Rojo',
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto = Producto::where('nombre', 'Producto sin foto')->first();
        $this->assertNotNull($producto);
        $this->assertNull($producto->imagen);
    }

    public function test_json_save_has_explicit_success_without_changing_blade_redirects(): void
    {
        $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Desde React', 'categoria_id' => $this->categoria->id,
            'precio_compra' => 10, 'precio_venta' => 20, 'cantidad' => 5,
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('saved', true)->assertJsonPath('redirect', route('productos.index'));

        $this->post(route('productos.store'), ['nombre' => ''], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors(['nombre']);

        $producto = Producto::where('nombre', 'Desde React')->firstOrFail();
        $this->put(route('productos.update', $producto), [
            'stock_original' => $producto->cantidad,
            'nombre' => 'Actualizado', 'categoria_id' => $this->categoria->id,
            'precio_compra' => 10, 'precio_venta' => 20, 'cantidad' => 5,
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('saved', true);
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'nombre' => 'Actualizado']);
    }

    public function test_update_without_reupload_preserves_existing_imagen_path(): void
    {
        // Create a product with an existing image path (simulating a prior upload).
        $producto = Producto::factory()->conImagen()->create([
            'categoria_id' => $this->categoria->id,
        ]);
        $originalPath = $producto->imagen;

        $response = $this->actingAs($this->admin)->put(route('productos.update', $producto), [
            'stock_original' => $producto->cantidad,
            'nombre' => $producto->nombre,
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 12.00,
            'precio_venta' => 30.00,
            'cantidad' => 10,
            'talle' => 'L',
            'color' => 'Azul',
            // No 'imagen' file sent → must preserve existing path.
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto->refresh();
        $this->assertSame($originalPath, $producto->imagen, 'Update without re-upload must not null-out imagen.');
    }

    public function test_update_with_new_image_replaces_existing_path(): void
    {
        $producto = Producto::factory()->conImagen()->create([
            'categoria_id' => $this->categoria->id,
        ]);
        $originalPath = $producto->imagen;

        Storage::disk('public')->put($originalPath, 'original image');
        $newFile = $this->pngImage(1, 'nueva.png');

        $response = $this->actingAs($this->admin)->put(route('productos.update', $producto), [
            'stock_original' => $producto->cantidad,
            'nombre' => $producto->nombre,
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 12.00,
            'precio_venta' => 30.00,
            'cantidad' => 10,
            'talle' => 'L',
            'color' => 'Azul',
            'imagen' => $newFile,
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto->refresh();
        $this->assertSame('database/'.$producto->id, $producto->imagen);
        $this->assertDatabaseHas('producto_imagenes', ['producto_id' => $producto->id]);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_failed_product_update_does_not_persist_image_bytes_or_remove_legacy_source(): void
    {
        $producto = Producto::factory()->conImagen()->create(['categoria_id' => $this->categoria->id]);
        $originalPath = $producto->imagen;
        Storage::disk('public')->put($originalPath, 'original image');
        Producto::updating(function () { throw new \RuntimeException('Persistence failed'); });

        try {
            $this->actingAs($this->admin)->put(route('productos.update', $producto), [
                'stock_original' => $producto->cantidad,
                'nombre' => 'Changed', 'categoria_id' => $this->categoria->id,
                'precio_compra' => 12, 'precio_venta' => 30, 'cantidad' => 10,
                'imagen' => $this->pngImage(),
            ])->assertStatus(500);
        } finally {
            Producto::flushEventListeners();
        }

        $this->assertSame($originalPath, $producto->fresh()->imagen);
        Storage::disk('public')->assertExists($originalPath);
        $this->assertSame([$originalPath], Storage::disk('public')->files('productos'));
        $this->assertDatabaseMissing('producto_imagenes', ['producto_id' => $producto->id]);
    }

    public function test_show_renders_image_thumbnail_when_imagen_present(): void
    {
        $producto = Producto::factory()->conImagen()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        // Create the fake file so Storage::url resolves to a real asset.
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true
        );
        Storage::disk('public')->put($producto->imagen, $png);

        $response = $this->actingAs($this->admin)->get(route('productos.show', $producto));

        $response->assertOk();
        $response->assertSee('<img', false);
        $response->assertSee(route('productos.imagen', $producto));
    }

    public function test_show_emits_no_product_image_when_imagen_is_null(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'imagen' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('productos.show', $producto));

        $response->assertOk();
        $response->assertDontSee('alt="'.$producto->nombre.'"', false);
    }

    public function test_data_endpoint_returns_imagen_field_for_client_thumbnails(): void
    {
        $producto = Producto::factory()->conImagen()->create([
            'categoria_id' => $this->categoria->id,
        ]);
        $file = $this->pngImage();
        $bytes = file_get_contents($file->getPathname());
        $sha1 = sha1($bytes);
        ProductoImagen::create(['producto_id' => $producto->id, 'bytes' => $bytes, 'mime' => 'image/png', 'sha1' => $sha1]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.data'));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $producto->id, 'imagen' => route('productos.imagen', $producto).'?v='.$sha1]);
        $this->assertStringNotContainsString(base64_encode($bytes), $response->getContent());
    }

    public function test_database_image_endpoint_is_authenticated_and_supports_etags(): void
    {
        $producto = Producto::factory()->create(['categoria_id' => $this->categoria->id, 'imagen' => 'database/1']);
        $file = $this->pngImage();
        $bytes = file_get_contents($file->getPathname());
        $sha1 = sha1($bytes);
        ProductoImagen::create(['producto_id' => $producto->id, 'bytes' => $bytes, 'mime' => 'image/png', 'sha1' => $sha1]);

        $url = route('productos.imagen', $producto);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $response = $this->actingAs($this->admin)->get($url);
        $response->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('ETag', '"'.$sha1.'"')
            ->assertHeader('Cache-Control')->assertContent($bytes);
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=31536000', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control'));
        $this->actingAs($this->admin)->get($url, ['If-None-Match' => '"'.$sha1.'"'])->assertStatus(304);
    }

    public function test_image_import_is_idempotent_and_preserves_source_file(): void
    {
        $file = $this->pngImage();
        $bytes = file_get_contents($file->getPathname());
        $path = 'productos/import.png';
        Storage::disk('public')->put($path, $bytes);
        $producto = Producto::factory()->create(['categoria_id' => $this->categoria->id, 'imagen' => $path]);

        $this->artisan('productos:import-images')->assertSuccessful();
        $this->artisan('productos:import-images')->assertSuccessful();

        $this->assertSame(1, ProductoImagen::where('producto_id', $producto->id)->count());
        Storage::disk('public')->assertExists($path);
        $this->assertSame($bytes, Storage::disk('public')->get($path));
    }
}
