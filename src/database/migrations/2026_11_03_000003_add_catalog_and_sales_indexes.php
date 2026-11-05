<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('categorias')->selectRaw('LOWER(nombre) as normalized_name')
            ->groupBy('normalized_name')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new LogicException('Resolve duplicate category names before applying the unique category index.');
        }

        Schema::table('ventas', function (Blueprint $table): void {
            $table->index(['estado', 'created_at']);
        });
        Schema::table('productos', function (Blueprint $table): void {
            $table->index(['activo', 'cantidad']);
            $table->index('nombre');
        });
        Schema::table('categorias', function (Blueprint $table): void {
            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table): void {
            $table->dropUnique(['nombre']);
        });
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropIndex(['activo', 'cantidad']);
            $table->dropIndex(['nombre']);
        });
        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropIndex(['estado', 'created_at']);
        });
    }
};
