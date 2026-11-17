<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('The demo seeder is disabled in production. Create the first administrator through an approved setup procedure.');
        }

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);
    }
}
