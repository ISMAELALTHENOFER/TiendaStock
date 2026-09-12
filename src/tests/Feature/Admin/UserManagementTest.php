<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_users(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_gets_403_from_admin_users(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_user_can_view_users_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertViewHas('users');
        // The users list is a React surface; assert the mount instead of
        // Blade-rendered fields (see UserReactViewContractTest for the full
        // coexistence contract).
        $response->assertSeeHtml('id="react-root"');
    }

    public function test_admin_user_can_view_create_form(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk();
    }

    public function test_admin_can_create_a_new_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'Ventas',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'Ventas',
        ]);
    }

    public function test_admin_cannot_create_user_with_duplicate_username(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['username' => 'existing']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Another User',
            'username' => 'existing',
            'email' => 'another@example.com',
            'role' => 'Ventas',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertOk();
        // React surface (see UserReactViewContractTest for the coexistence contract).
        $response->assertSeeHtml('id="react-root"');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'username' => 'updateduser',
            'email' => 'updated@example.com',
            'role' => 'Ventas',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updateduser', $user->username);
        $this->assertSame('updated@example.com', $user->email);
    }

    public function test_regular_user_cannot_access_admin_create(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_create_users(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->post('/admin/users', [
            'name' => 'Hacker',
            'username' => 'hacker',
            'email' => 'hacker@example.com',
            'role' => 'ADMIN',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_without_changing_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Original Name',
            'username' => 'originaluser',
            'email' => 'original@example.com',
        ]);
        $originalPassword = $user->password;

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'username' => 'originaluser',
            'email' => 'original@example.com',
            'role' => 'Ventas',
        ]);

        $response->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame($originalPassword, $user->password);
    }
}
