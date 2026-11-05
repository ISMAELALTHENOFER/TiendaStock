<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')->where('activo', false)->where('cantidad', '>', 0)->update(['activo' => true]);
    }

    public function down(): void
    {
        // The previous state cannot be recovered without discarding later changes.
    }
};
