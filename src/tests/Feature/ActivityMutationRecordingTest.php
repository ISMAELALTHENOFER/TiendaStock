<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class ActivityMutationRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshTestDatabase(): void
    {
        $this->artisan('migrate:fresh');
        $this->updateLocalCacheOfInMemoryDatabases();
        RefreshDatabaseState::$migrated = true;
    }

    public function test_successful_sale_records_activity(): void
    {
        $user = User::factory()->ventas()->create();
        $producto = Producto::factory()->create(['cantidad' => 3, 'precio_venta' => 20]);

        $this->actingAs($user)->post('/ventas', [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 20, 'total' => 20, 'pago_con' => 20,
            'metodo_pago' => 'efectivo', 'tipo_entrega' => 'local',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_events', ['actor_id' => $user->id, 'type' => 'sale.created']);
    }

    public function test_failed_sale_does_not_record_activity(): void
    {
        $user = User::factory()->ventas()->create();

        $this->actingAs($user)->post('/ventas', [
            'items' => [], 'subtotal' => 0, 'total' => 0, 'pago_con' => 0,
            'metodo_pago' => 'efectivo', 'tipo_entrega' => 'local',
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('activity_events', 0);
    }

    public function test_successful_sale_cancellation_records_activity(): void
    {
        $user = User::factory()->ventas()->create();
        $producto = Producto::factory()->create(['cantidad' => 1]);
        $venta = Venta::factory()->create(['user_id' => $user->id]);
        $venta->items()->create([
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => $producto->precio_venta,
            'subtotal' => $producto->precio_venta,
        ]);

        $this->actingAs($user)->post("/ventas/{$venta->id}/cancel")->assertRedirect();

        $this->assertDatabaseHas('activity_events', [
            'actor_id' => $user->id,
            'type' => 'sale.cancelled',
            'subject_id' => $venta->id,
        ]);
    }

    public function test_successful_product_category_and_user_mutations_record_activity(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create(['nombre' => 'Original']);

        $this->actingAs($admin)->post('/categorias', ['nombre' => 'Nueva', 'descripcion' => ''])->assertRedirect();
        $this->actingAs($admin)->post('/productos', [
            'nombre' => 'Camisa', 'categoria_id' => $categoria->id, 'precio_compra' => 10,
            'precio_venta' => 20, 'cantidad' => 4, 'talle' => 'M', 'color' => 'Azul',
        ])->assertRedirect();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User', 'username' => 'new-user', 'email' => 'new@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123', 'role' => User::ROLE_VENTAS,
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_events', ['type' => 'category.created']);
        $this->assertDatabaseHas('activity_events', ['type' => 'product.created']);
        $this->assertDatabaseHas('activity_events', ['type' => 'user.created']);
    }

    public function test_failed_product_mutation_does_not_record_activity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/productos', [])->assertSessionHasErrors();

        $this->assertDatabaseCount('activity_events', 0);
    }

    public function test_failed_category_mutation_does_not_record_activity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/categorias', [])->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('activity_events', 0);
    }

    public function test_failed_user_mutation_does_not_record_activity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/users', [])->assertSessionHasErrors();

        $this->assertDatabaseCount('activity_events', 0);
    }

    #[RunInSeparateProcess]
    public function test_product_create_rolls_back_when_activity_recording_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        Mockery::mock('alias:App\Services\ActivityRecorder')
            ->shouldReceive('recordAfterCommit')->once()->andThrow(new \RuntimeException('forced failure'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post('/productos', [
                'nombre' => 'Rolled Back Product', 'categoria_id' => $categoria->id,
                'precio_compra' => 10, 'precio_venta' => 20, 'cantidad' => 4,
            ]);
        } catch (\RuntimeException $exception) {
            $this->assertSame('forced failure', $exception->getMessage());
        }

        $this->assertDatabaseMissing('productos', ['nombre' => 'Rolled Back Product']);
        $this->assertDatabaseCount('activity_events', 0);
    }

    #[RunInSeparateProcess]
    public function test_category_create_rolls_back_when_activity_recording_fails(): void
    {
        $admin = User::factory()->admin()->create();
        Mockery::mock('alias:App\Services\ActivityRecorder')
            ->shouldReceive('recordAfterCommit')->once()->andThrow(new \RuntimeException('forced failure'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post('/categorias', ['nombre' => 'Rolled Back Category']);
        } catch (\RuntimeException $exception) {
            $this->assertSame('forced failure', $exception->getMessage());
        }

        $this->assertDatabaseMissing('categorias', ['nombre' => 'Rolled Back Category']);
        $this->assertDatabaseCount('activity_events', 0);
    }

    #[RunInSeparateProcess]
    public function test_user_create_rolls_back_when_activity_recording_fails(): void
    {
        $admin = User::factory()->admin()->create();
        Mockery::mock('alias:App\Services\ActivityRecorder')
            ->shouldReceive('recordAfterCommit')->once()->andThrow(new \RuntimeException('forced failure'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post('/admin/users', [
                'name' => 'Rolled Back User', 'username' => 'rolled-back-user',
                'email' => 'rolled-back@example.com', 'password' => 'password123',
                'password_confirmation' => 'password123', 'role' => User::ROLE_VENTAS,
            ]);
        } catch (\RuntimeException $exception) {
            $this->assertSame('forced failure', $exception->getMessage());
        }

        $this->assertDatabaseMissing('users', ['username' => 'rolled-back-user']);
        $this->assertDatabaseCount('activity_events', 0);
    }

    #[RunInSeparateProcess]
    public function test_product_update_preserves_original_values_when_activity_recording_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'nombre' => 'Original Product', 'categoria_id' => $categoria->id,
            'precio_compra' => 10, 'precio_venta' => 20,
        ]);
        Mockery::mock('alias:App\Services\ActivityRecorder')
            ->shouldReceive('recordAfterCommit')->once()->andThrow(new \RuntimeException('forced failure'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->put("/productos/{$producto->id}", [
                'nombre' => 'Changed Product', 'categoria_id' => $categoria->id,
                'precio_compra' => 11, 'precio_venta' => 21, 'cantidad' => 5,
            ]);
        } catch (\RuntimeException $exception) {
            $this->assertSame('forced failure', $exception->getMessage());
        }

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id, 'nombre' => 'Original Product',
            'precio_compra' => 10, 'precio_venta' => 20,
        ]);
        $this->assertDatabaseCount('activity_events', 0);
    }
}
