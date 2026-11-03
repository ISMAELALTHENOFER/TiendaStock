<?php

namespace Tests\MySQL;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use DatabaseMigrations;

    public function test_mysql_enforces_sales_history_foreign_keys(): void
    {
        $user = User::factory()->ventas()->create();
        $category = Categoria::factory()->create();
        $product = Producto::factory()->create(['categoria_id' => $category->id]);
        $sale = Venta::factory()->create(['user_id' => $user->id]);
        $item = VentaItem::factory()->create(['venta_id' => $sale->id, 'producto_id' => $product->id]);

        try {
            $product->delete();
            $this->fail('MySQL should restrict deletion of products referenced by sale items.');
        } catch (QueryException) {
            $this->assertDatabaseHas('venta_items', ['id' => $item->id]);
        }

        try {
            $user->delete();
            $this->fail('MySQL should restrict deletion of users referenced by sales.');
        } catch (QueryException) {
            $this->assertDatabaseHas('ventas', ['id' => $sale->id]);
        }
    }

    public function test_mysql_row_lock_serializes_competing_sale_updates(): void
    {
        $category = Categoria::factory()->create();
        $product = Producto::factory()->create(['categoria_id' => $category->id]);
        $primary = DB::connection();
        config(['database.connections.mysql_lock_test' => config('database.connections.mysql')]);
        DB::purge('mysql_lock_test');
        $secondary = DB::connection('mysql_lock_test');

        $primary->beginTransaction();
        try {
            Producto::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $secondary->statement('SET SESSION innodb_lock_wait_timeout = 1');
            $secondary->beginTransaction();
            try {
                $secondary->table('productos')->where('id', $product->id)->lockForUpdate()->first();
                $this->fail('The second MySQL transaction should wait for the row lock.');
            } catch (QueryException $exception) {
                $this->assertSame(1205, (int) ($exception->errorInfo[1] ?? 0));
            } finally {
                $secondary->rollBack();
            }
        } finally {
            $primary->rollBack();
            DB::purge('mysql_lock_test');
        }
    }
}
