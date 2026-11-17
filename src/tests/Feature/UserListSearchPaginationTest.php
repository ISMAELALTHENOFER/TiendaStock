<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListSearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_search_matches_name_username_and_email(): void
    {
        $admin = User::factory()->admin()->create();
        $byName = User::factory()->create(['name' => 'Name Search']);
        $byUsername = User::factory()->create(['username' => 'user_search']);
        $byEmail = User::factory()->create(['email' => 'email-search@example.com']);
        $this->actingAs($admin);

        foreach ([
            'Name Search' => $byName->id,
            'user_search' => $byUsername->id,
            'email-search@example.com' => $byEmail->id,
        ] as $search => $expectedId) {
            $response = $this->get('/admin/users?buscar='.urlencode($search));

            $response->assertOk()->assertViewHas('users', function ($users) use ($expectedId) {
                return $users->total() === 1 && $users->first()->id === $expectedId;
            });
        }
    }

    public function test_user_page_size_defaults_to_15_and_accepts_only_15_30_or_50(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(51)->create();
        $this->actingAs($admin);

        foreach ([null => 15, '15' => 15, '30' => 30, '50' => 50, '100' => 15] as $perPage => $expected) {
            $url = $perPage === '' ? '/admin/users' : '/admin/users?per_page='.$perPage;
            $this->get($url)->assertOk()->assertViewHas('users', function ($users) use ($expected) {
                return $users->perPage() === $expected;
            });
        }
    }

    public function test_user_search_and_page_size_are_retained_in_pagination_urls(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(31)->create(['name' => 'Retained Search']);
        $this->actingAs($admin);

        $response = $this->get('/admin/users?buscar=Retained%20Search&per_page=15');

        $response->assertOk()->assertViewHas('users', function ($users) {
            $url = $users->url(2);

            return $users->total() === 31
                && str_contains($url, 'buscar=Retained%20Search')
                && str_contains($url, 'per_page=15')
                && str_contains($url, 'page=2');
        });
    }
}
