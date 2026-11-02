<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryValidatedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_ignore_unvalidated_attributes(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Categoria::unguard();

        try {
            $this->postJson(route('categorias.store'), [
                'nombre' => 'Shoes', 'descripcion' => 'Original', 'id' => 98765,
            ])->assertOk();

            $category = Categoria::where('nombre', 'Shoes')->firstOrFail();
            $this->assertNotEquals(98765, $category->id);
            $id = $category->id;

            $this->putJson(route('categorias.update', $category), [
                'nombre' => 'Footwear', 'descripcion' => 'Updated', 'id' => 98766,
            ])->assertOk();

            $this->assertDatabaseHas('categorias', ['id' => $id, 'nombre' => 'Footwear', 'descripcion' => 'Updated']);
            $this->assertDatabaseMissing('categorias', ['id' => 98766]);
        } finally {
            Categoria::reguard();
        }
    }
}
