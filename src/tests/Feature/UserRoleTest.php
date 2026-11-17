<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_admin_method_returns_true(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $this->assertTrue($user->isAdmin());
    }

    public function test_ventas_user_is_ventas_method_returns_true(): void
    {
        $user = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($user->isVentas());
    }

    public function test_control_stock_user_is_control_stock_method_returns_true(): void
    {
        $user = User::factory()->create(['role' => 'Control Stock']);

        $this->assertTrue($user->isControlStock());
    }

    public function test_admin_user_is_not_ventas(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $this->assertFalse($user->isVentas());
        $this->assertFalse($user->isControlStock());
    }

    public function test_has_role_with_single_role(): void
    {
        $user = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($user->hasRole('Ventas'));
        $this->assertFalse($user->hasRole('ADMIN'));
    }

    public function test_has_role_with_array_of_roles(): void
    {
        $user = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($user->hasRole(['ADMIN', 'Ventas']));
        $this->assertFalse($user->hasRole(['ADMIN', 'Control Stock']));
    }

    public function test_is_at_least_admin_passes_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $ventas = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($admin->isAtLeast('ADMIN'));
        $this->assertFalse($ventas->isAtLeast('ADMIN'));
    }

    public function test_is_at_least_control_stock_passes_for_admin_and_control_stock(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $cs = User::factory()->create(['role' => 'Control Stock']);
        $ventas = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($admin->isAtLeast('Control Stock'));
        $this->assertTrue($cs->isAtLeast('Control Stock'));
        $this->assertFalse($ventas->isAtLeast('Control Stock'));
    }

    public function test_is_at_least_ventas_passes_for_all(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $cs = User::factory()->create(['role' => 'Control Stock']);
        $ventas = User::factory()->create(['role' => 'Ventas']);

        $this->assertTrue($admin->isAtLeast('Ventas'));
        $this->assertTrue($cs->isAtLeast('Ventas'));
        $this->assertTrue($ventas->isAtLeast('Ventas'));
    }

    public function test_role_constants_are_defined(): void
    {
        $this->assertSame('ADMIN', User::ROLE_ADMIN);
        $this->assertSame('Ventas', User::ROLE_VENTAS);
        $this->assertSame('Control Stock', User::ROLE_CONTROL_STOCK);
    }

    public function test_available_roles_returns_all_roles(): void
    {
        $roles = User::availableRoles();

        $this->assertContains('ADMIN', $roles);
        $this->assertContains('Ventas', $roles);
        $this->assertContains('Control Stock', $roles);
    }
}
