<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $usedUsernames = DB::table('users')->whereNotNull('username')->pluck('username')
            ->mapWithKeys(fn ($username) => [mb_strtolower($username) => true])->all();

        DB::table('users')->whereNull('username')->orderBy('id')->each(function ($user) use (&$usedUsernames) {
            $prefix = Str::slug(explode('@', $user->email, 2)[0]);
            $prefix = $prefix !== '' ? $prefix : 'user-'.$user->id;
            $username = $prefix;
            $suffix = 1;

            while (isset($usedUsernames[mb_strtolower($username)])) {
                $username = $prefix.$suffix++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            $usedUsernames[mb_strtolower($username)] = true;
        });

        // Now enforce not-nullable
        Schema::table('users', function ($table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->string('username')->nullable()->change();
        });
    }
};
