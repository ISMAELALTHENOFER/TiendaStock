<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use LogicException;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    public function test_demo_database_seeder_refuses_to_run_in_production(): void
    {
        config(['app.env' => 'production']);
        $this->app['env'] = 'production';

        $this->expectException(LogicException::class);
        app(DatabaseSeeder::class)->run();
    }
}
