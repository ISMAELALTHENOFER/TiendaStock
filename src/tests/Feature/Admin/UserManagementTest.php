<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Venta;
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

    public function test_admin_users_index_filters_inactive_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['activo' => true]);
        $inactive = User::factory()->create(['activo' => false]);

        $response = $this->actingAs($admin)->getJson('/admin/users?inactivos=1');

        $response->assertOk()->assertJsonFragment(['id' => $inactive->id])->assertJsonMissing(['id' => $admin->id]);
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

    public function test_admin_can_reset_another_users_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name, 'username' => $user->username, 'email' => $user->email,
            'role' => 'Ventas', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertRedirect('/admin/users');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $user->refresh()->password));
    }

    public function test_admin_can_activate_and_deactivate_other_accounts_without_deleting_history(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->ventas()->create();
        $sale = Venta::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)->post("/admin/users/{$user->id}/deactivate")->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'activo' => false]);
        $this->assertDatabaseHas('ventas', ['id' => $sale->id, 'user_id' => $user->id]);
        $this->post("/admin/users/{$user->id}/activate")->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'activo' => true]);
    }

    public function test_admin_cannot_deactivate_self_or_last_active_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post("/admin/users/{$admin->id}/deactivate")->assertForbidden();
        $this->post('/admin/users', [
            'name' => 'Another Admin', 'username' => 'anotheradmin', 'email' => 'another@example.com',
            'role' => 'ADMIN', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect('/admin/users');
        $admin->refresh();
        $this->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'username' => $admin->username, 'email' => $admin->email, 'role' => 'Ventas',
        ])->assertForbidden();
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'username' => $admin->username, 'email' => $admin->email, 'role' => 'Ventas',
        ])->assertForbidden();
    }

    public function test_admin_only_can_change_account_active_state(): void
    {
        $user = User::factory()->ventas()->create();
        $this->actingAs($user)->post("/admin/users/{$user->id}/deactivate")->assertForbidden();
        $this->assertTrue($user->refresh()->activo);
    }
}
