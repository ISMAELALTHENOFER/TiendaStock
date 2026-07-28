<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // 'local' = venta en el local; 'uber' = envio por Uber.
            // Nullable: las ventas preexistentes no tenian este dato.
            // No hay default intencional: el POS debe elegir uno obligatoriamente.
            $table->string('tipo_entrega', 20)->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('tipo_entrega');
        });
    }
};