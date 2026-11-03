<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SaleHistoryForeignKeyMigrationTest extends TestCase
{
    public function test_incompatible_existing_key_is_rejected_before_either_table_changes(): void
    {
        $migration = require database_path('migrations/2026_11_02_000001_restrict_sale_history_parent_deletes.php');
        $migration->down();
        Schema::disableForeignKeyConstraints();
        try {
            Schema::table('venta_items', function (Blueprint $table) {
                $table->dropForeign(['producto_id']);
                $table->foreign('producto_id')->references('id')->on('users')->cascadeOnDelete();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        try {
            $migration->up();
            $this->fail('An incompatible foreign key must be rejected.');
        } catch (\RuntimeException $exception) {
            $this->assertNotEmpty($exception->getMessage());
        }
        $sales = collect(Schema::getForeignKeys('ventas'))->keyBy(fn ($key) => $key['columns'][0]);
        $items = collect(Schema::getForeignKeys('venta_items'))->keyBy(fn ($key) => $key['columns'][0]);
        $this->assertSame('cascade', strtolower($sales['user_id']['on_delete']));
        $this->assertSame('users', $items['producto_id']['foreign_table']);
    }

    public function test_retry_repairs_a_missing_second_key_after_the_first_was_replaced(): void
    {
        $migration = require database_path('migrations/2026_11_02_000001_restrict_sale_history_parent_deletes.php');
        $migration->down();
        $user = User::factory()->create();
        $product = Producto::factory()->create();
        $sale = Venta::factory()->create(['user_id' => $user->id]);
        $item = VentaItem::factory()->create(['venta_id' => $sale->id, 'producto_id' => $product->id]);
        $indexes = Schema::getIndexes('venta_items');

        Schema::disableForeignKeyConstraints();
        try {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            });
            Schema::table('venta_items', fn (Blueprint $table) => $table->dropForeign(['producto_id']));
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $migration->up();
        $this->assertDatabaseHas('ventas', ['id' => $sale->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('venta_items', ['id' => $item->id, 'venta_id' => $sale->id, 'producto_id' => $product->id]);
        $sales = collect(Schema::getForeignKeys('ventas'))->keyBy(fn ($key) => $key['columns'][0]);
        $items = collect(Schema::getForeignKeys('venta_items'))->keyBy(fn ($key) => $key['columns'][0]);
        $this->assertSame('restrict', strtolower($sales['user_id']['on_delete']));
        $this->assertSame('restrict', strtolower($items['producto_id']['on_delete']));
        $this->assertSame('cascade', strtolower($items['venta_id']['on_delete']));
        $this->assertSame($indexes, Schema::getIndexes('venta_items'));
    }

    public function test_retry_rejects_orphans_before_changing_the_first_key(): void
    {
        $migration = require database_path('migrations/2026_11_02_000001_restrict_sale_history_parent_deletes.php');
        $migration->down();
        $user = User::factory()->create();
        $product = Producto::factory()->create();
        $sale = Venta::factory()->create(['user_id' => $user->id]);
        $item = VentaItem::factory()->create(['venta_id' => $sale->id, 'producto_id' => $product->id]);
        Schema::disableForeignKeyConstraints();
        try {
            Schema::table('venta_items', fn (Blueprint $table) => $table->dropForeign(['producto_id']));
            DB::table('venta_items')->where('id', $item->id)->update(['producto_id' => 999999]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
        try {
            $migration->up();
            $this->fail('Orphans must be rejected.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Orphaned sale history', $exception->getMessage());
        }
        $sales = collect(Schema::getForeignKeys('ventas'))->keyBy(fn ($key) => $key['columns'][0]);
        $this->assertSame('cascade', strtolower($sales['user_id']['on_delete']));
        $this->assertDatabaseHas('venta_items', ['id' => $item->id, 'producto_id' => 999999]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Migration lifecycle test requires an isolated in-memory SQLite database.');
        }

        $this->artisan('migrate:fresh')->assertExitCode(0);
        RefreshDatabaseState::$migrated = false;
    }

    public function test_upgrade_and_rollback_preserve_existing_sales_and_items(): void
    {
        $migration = require database_path('migrations/2026_11_02_000001_restrict_sale_history_parent_deletes.php');
        $migration->down();

        $user = User::factory()->create();
        $product = Producto::factory()->create();
        $sale = Venta::factory()->create(['user_id' => $user->id]);
        $item = VentaItem::factory()->create(['venta_id' => $sale->id, 'producto_id' => $product->id]);

        foreach (['up' => 'restrict', 'down' => 'cascade', 'up_again' => 'restrict'] as $operation => $expected) {
            $operation === 'down' ? $migration->down() : $migration->up();

            $this->assertDatabaseHas('ventas', ['id' => $sale->id, 'user_id' => $user->id]);
            $this->assertDatabaseHas('venta_items', ['id' => $item->id, 'venta_id' => $sale->id, 'producto_id' => $product->id]);

            $saleKeys = collect(Schema::getForeignKeys('ventas'))->keyBy(fn ($key) => $key['columns'][0]);
            $itemKeys = collect(Schema::getForeignKeys('venta_items'))->keyBy(fn ($key) => $key['columns'][0]);
            $this->assertSame($expected, strtolower($saleKeys['user_id']['on_delete']));
            $this->assertSame($expected, strtolower($itemKeys['producto_id']['on_delete']));
            $this->assertSame('cascade', strtolower($itemKeys['venta_id']['on_delete']));
        }
    }

    public function test_upgrade_does_not_change_constraints_when_a_table_is_missing(): void
    {
        $migration = require database_path('migrations/2026_11_02_000001_restrict_sale_history_parent_deletes.php');
        $migration->down();
        Schema::rename('venta_items', 'unavailable_venta_items');

        try {
            $migration->up();
            $this->fail('The upgrade should reject an incomplete schema.');
        } catch (\RuntimeException|\Illuminate\Database\QueryException $exception) {
            $this->assertNotEmpty($exception->getMessage());
        }

        $keys = collect(Schema::getForeignKeys('ventas'))->keyBy(fn ($key) => $key['columns'][0]);
        $this->assertSame('cascade', strtolower($keys['user_id']['on_delete']));
    }
}
