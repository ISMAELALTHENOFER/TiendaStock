<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsernameBackfillMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Migration lifecycle test requires an isolated in-memory SQLite database.');
        }

        $this->artisan('migrate:fresh')->assertExitCode(0);
        RefreshDatabaseState::$migrated = false;
    }

    public function test_backfill_generates_unique_usernames_without_overwriting_existing_values(): void
    {
        $migration = require database_path('migrations/2026_07_28_000002_backfill_usernames.php');
        $migration->down();

        foreach ([
            ['First', 'same@example.com', null],
            ['Second', 'same@another.com', null],
            ['Empty', '@example.com', null],
            ['Existing', 'same@third.com', 'Same'],
        ] as [$name, $email, $username]) {
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'username' => $username,
            ]);
        }

        $migration->up();

        $this->assertSame(['same1', 'same2', 'user-3', 'Same'], DB::table('users')->orderBy('id')->pluck('username')->all());

        $migration->down();
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertSame(['same1', 'same2', 'user-3', 'Same'], DB::table('users')->orderBy('id')->pluck('username')->all());
    }
}
