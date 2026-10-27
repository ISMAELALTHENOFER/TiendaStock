<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractiveGridSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_blade_grid_searches_update_results_in_place_from_two_characters_and_keep_manual_fallback(): void
    {
        config(['frontend.driver' => 'blade']);
        $admin = User::factory()->admin()->create();
        $salesUser = User::factory()->ventas()->create();
        $categoria = Categoria::factory()->create();

        foreach ([
            ['/admin/users?buscar=us&per_page=30', $admin, 'users-search'],
            ['/categorias?buscar=ca&per_page=30', $admin, 'category-search'],
            ['/categorias/'.$categoria->id.'?buscar=bu&per_page=30', $admin, 'category-product-search'],
            ['/productos', $admin, null],
            ['/ventas?buscar=42&desde=2026-01-01&hasta=2026-10-25&estado=completada&per_page=30', $salesUser, 'sales-search'],
        ] as [$url, $user, $inputId]) {
            $response = $this->actingAs($user)->get($url)->assertOk();
            $html = $response->getContent();

            if ($inputId !== null) {
                $this->assertStringContainsString('id="'.$inputId.'"', $html);
                $this->assertStringContainsString('data-live-search="true"', $html, "Expected {$inputId} to update results in place.");
                $this->assertStringContainsString('data-live-results=', $html, "Expected {$inputId} to target a results region.");
                $this->assertStringContainsString('type="submit"', $html);
                $this->assertStringContainsString('name="per_page" value="30"', $html);
            }
        }

        $sales = $this->actingAs($salesUser)
            ->get('/ventas?buscar=42&desde=2026-01-01&hasta=2026-10-25&estado=completada&per_page=30')
            ->assertOk()->getContent();
        $this->assertStringContainsString('name="desde" value="2026-01-01"', $sales);
        $this->assertStringContainsString('name="hasta" value="2026-10-25"', $sales);
        $this->assertStringContainsString('name="estado"', $sales);
        $this->assertStringContainsString('value="completada" selected', $sales);

        $products = $this->actingAs($admin)->get('/productos')->assertOk()->getContent();
        $this->assertStringContainsString("termino.length >= 2", $products);
        $this->assertStringContainsString('Buscar por nombre, categoría, talle o color', $products);
    }

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
