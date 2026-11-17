<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->replaceConstraints('restrict');
    }

    public function down(): void
    {
        $this->replaceConstraints('cascade');
    }

    private function replaceConstraints(string $onDelete): void
    {
        if (! Schema::hasTable('ventas') || ! Schema::hasTable('venta_items')) {
            throw new RuntimeException('Sale history tables must exist before replacing foreign keys.');
        }

        $sqlite = DB::getDriverName() === 'sqlite';
        if (! $sqlite && DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('Unsupported database driver for sale history foreign keys.');
        }
        if ($sqlite && DB::transactionLevel() > 0) {
            throw new RuntimeException('SQLite foreign keys cannot be safely rebuilt inside a transaction.');
        }

        $constraints = [];
        foreach (['ventas' => ['user_id', 'users'], 'venta_items' => ['producto_id', 'productos']] as $table => [$column, $parent]) {
            $keys = collect(Schema::getForeignKeys($table))->filter(fn ($key) => in_array($column, $key['columns'], true))->values();
            if ($keys->count() > 1 || ($keys->count() === 1 && (
                $keys[0]['columns'] !== [$column] || $keys[0]['foreign_table'] !== $parent ||
                $keys[0]['foreign_columns'] !== ['id'] ||
                (! $sqlite && $keys[0]['foreign_schema'] !== DB::connection()->getDatabaseName()) ||
                ! in_array(strtolower($keys[0]['on_delete']), ['cascade', 'restrict', 'no action'], true) ||
                ! in_array(strtolower($keys[0]['on_update']), ['no action', 'restrict'], true) ||
                (! $sqlite && empty($keys[0]['name']))
            ))) {
                throw new RuntimeException("Incompatible foreign key on {$table}.{$column}.");
            }
            if (DB::table($table)->leftJoin($parent, "{$table}.{$column}", '=', "{$parent}.id")
                ->whereNotNull("{$table}.{$column}")->whereNull("{$parent}.id")->exists()) {
                throw new RuntimeException("Orphaned sale history on {$table}.{$column}.");
            }
            $constraints[$table] = [$column, $parent, $keys->first()];
        }

        $foreignKeysEnabled = $sqlite && (bool) DB::scalar('PRAGMA foreign_keys');
        if ($foreignKeysEnabled) {
            Schema::disableForeignKeyConstraints();
        }

        try {
            foreach ($constraints as $name => [$column, $parent, $key]) {
                if ($key && strtolower($key['on_delete']) === $onDelete) {
                    continue;
                }
                Schema::table($name, function (Blueprint $table) use ($column, $parent, $key, $onDelete, $sqlite) {
                    if ($key) {
                        $table->dropForeign($sqlite ? [$column] : $key['name']);
                    }
                    $table->foreign($column)->references('id')->on($parent)->onDelete($onDelete);
                });
            }
        } finally {
            if ($foreignKeysEnabled) {
                Schema::enableForeignKeyConstraints();
            }
        }
    }
};
