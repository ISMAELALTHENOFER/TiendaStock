<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->default(null)->after('is_admin');
        });

        // Backfill existing users: is_admin=true → ADMIN, others → Ventas
        DB::table('users')->where('is_admin', true)->update(['role' => 'ADMIN']);
        DB::table('users')->where('is_admin', false)->update(['role' => 'Ventas']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
