<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateFirstAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_prompts_for_unique_account_and_hides_password(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Name', 'Initial Admin')
            ->expectsQuestion('Username', 'initial-admin')
            ->expectsQuestion('Email', 'initial@example.com')
            ->expectsQuestion('Password', 'Correct-Horse-42')
            ->expectsQuestion('Confirm password', 'Correct-Horse-42')
            ->assertSuccessful();

        $admin = User::where('username', 'initial-admin')->firstOrFail();
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->activo);
        $this->assertNotSame('Correct-Horse-42', $admin->password);
        $this->assertTrue(password_verify('Correct-Horse-42', $admin->password));
    }

    public function test_admin_create_refuses_to_create_another_account_when_admin_exists(): void
    {
        User::factory()->admin()->create();

        $this->artisan('admin:create')->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }
}
