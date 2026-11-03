<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('profile.edit', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_can_request_verification_notification(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification')
            ->assertRedirect('/verify-email')->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_flows_redirect_by_role(): void
    {
        foreach (['ADMIN' => '/dashboard', 'Ventas' => '/ventas/pos', 'Control Stock' => '/productos', '' => '/profile', 'invalid' => '/profile'] as $role => $destination) {
            $user = User::factory()->unverified()->create(['role' => $role ?: null]);
            $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
                'id' => $user->id, 'hash' => sha1($user->email),
            ]);

            $this->actingAs($user)->get('/verify-email')->assertOk();
            $this->get($url)->assertRedirect($destination.'?verified=1');
            $this->assertTrue($user->fresh()->hasVerifiedEmail());
            $this->get($url)->assertRedirect($destination.'?verified=1');
            $this->get('/verify-email')->assertRedirect($destination);
            $this->post('/email/verification-notification')->assertRedirect($destination);
        }
    }

    public function test_verification_redirects_preserve_intended_url_and_authorization(): void
    {
        $user = User::factory()->ventas()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->withSession(['url.intended' => '/dashboard'])
            ->get($url)->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertForbidden();

        foreach ([['get', '/verify-email'], ['post', '/email/verification-notification'], ['get', $url]] as [$method, $path]) {
            $this->withSession(['url.intended' => '/dashboard'])->$method($path)->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertForbidden();
        }
    }
}
