<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_sales_indexes_are_installed(): void
    {
        $this->assertContains('ventas_estado_created_at_index', array_column(Schema::getIndexes('ventas'), 'name'));
        $this->assertContains('productos_activo_cantidad_index', array_column(Schema::getIndexes('productos'), 'name'));
        $this->assertContains('productos_nombre_index', array_column(Schema::getIndexes('productos'), 'name'));
        $this->assertContains('categorias_nombre_unique', array_column(Schema::getIndexes('categorias'), 'name'));
    }

    public function test_database_rejects_duplicate_category_names(): void
    {
        Categoria::factory()->create(['nombre' => 'Accesorios']);

        $this->expectException(QueryException::class);
        Categoria::factory()->create(['nombre' => 'Accesorios']);
    }
}
