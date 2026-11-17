<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()->assertSee('id="react-root"', false);
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('profile.edit', $props['page']);
        $this->assertSame($user->only(['name', 'username', 'email']), $props['userProfile']);
        $this->assertSame(route('profile.update'), $props['routes']['profile']);
        $this->assertSame(route('password.update'), $props['routes']['passwordUpdate']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('testuser', $user->username);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_user_cannot_delete_their_account_or_lose_their_sales(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_VENTAS, User::ROLE_CONTROL_STOCK] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $sale = Venta::factory()->create(['user_id' => $user->id]);
            $userBefore = $user->fresh()->getAttributes();
            $saleBefore = $sale->fresh()->getAttributes();

            foreach ([[], ['password' => 'wrong-password'], ['password' => 'password']] as $payload) {
                $this->actingAs($user)->delete('/profile', $payload)->assertStatus(405);
                $this->assertSame($userBefore, $user->fresh()->getAttributes());
                $this->assertSame($saleBefore, $sale->fresh()->getAttributes());
            }
        }
    }

}
