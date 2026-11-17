<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractiveGridSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_grid_search_endpoints_return_paginated_json(): void
    {
        $admin = User::factory()->admin()->create();
        $salesUser = User::factory()->ventas()->create();

        $this->actingAs($admin)->getJson('/categorias?buscar=ca&per_page=30')
            ->assertOk()->assertJsonStructure(['data', 'current_page', 'per_page', 'total'])->assertJsonPath('per_page', 30);
        $this->actingAs($admin)->getJson('/admin/users?buscar=us&per_page=30')
            ->assertOk()->assertJsonStructure(['data', 'current_page', 'per_page', 'total'])->assertJsonPath('per_page', 30);
        $this->actingAs($salesUser)->getJson('/ventas?buscar=42&per_page=30')
            ->assertOk()->assertJsonStructure(['data', 'current_page', 'per_page', 'total'])->assertJsonPath('per_page', 30);
    }
}
