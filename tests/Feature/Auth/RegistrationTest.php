<?php

namespace Tests\Feature\Auth;

use App\Auth\Services\RegistrationInviteService;
use App\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config(['registration.invite' => null]);
    }

    #[Test]
    public function registration_is_closed_without_invite(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/register?invite=nope')->assertNotFound();
    }

    #[Test]
    public function master_invite_can_register_admin(): void
    {
        config(['registration.invite' => 'master-secret', 'registration.invite_role' => 'admin']);

        $this->get('/register?invite=master-secret')->assertOk();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => 'master-secret',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(User::where('email', 'test@example.com')->first()->hasRole('admin'));
    }

    #[Test]
    public function one_time_invite_can_register_and_is_consumed(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin, 'user', 'buddy', 7);

        $this->post('/register', [
            'name' => 'Buddy',
            'email' => 'buddy@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->token,
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'buddy@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertNotNull($invite->fresh()->used_at);

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Reuse',
            'email' => 'reuse@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->token,
        ])->assertNotFound();
    }

    #[Test]
    public function revoked_invite_is_rejected(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin);
        app(RegistrationInviteService::class)->revoke($invite);

        $this->get('/register?invite='.$invite->token)->assertNotFound();
    }

    #[Test]
    public function expired_invite_is_rejected_on_register(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin, 'user');
        $invite->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->get('/register?invite='.$invite->token)->assertNotFound();

        $this->post('/register', [
            'name' => 'Expired',
            'email' => 'expired@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->token,
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertNull(User::where('email', 'expired@example.com')->first());
    }

    #[Test]
    public function registration_consumes_invite_atomically_with_user(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin, 'user');

        $this->post('/register', [
            'name' => 'Atomic',
            'email' => 'atomic@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->token,
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'atomic@example.com')->firstOrFail();
        $invite->refresh();

        $this->assertNotNull($invite->used_at);
        $this->assertSame($user->id, $invite->used_by);
        $this->assertTrue($user->hasRole('user'));
    }

    #[Test]
    public function registration_rejects_invite_with_disallowed_role(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin, 'user');
        $invite->forceFill(['role' => 'superadmin'])->save();

        $this->post('/register', [
            'name' => 'Bad Role',
            'email' => 'badrole@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->token,
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertNull(User::where('email', 'badrole@example.com')->first());
    }

    #[Test]
    public function registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/register', [
                'name' => "User {$i}",
                'email' => "ratelimit{$i}@example.com",
                'password' => 'password',
                'password_confirmation' => 'password',
                'invite' => 'nope',
            ]);
        }

        $this->post('/register', [
            'name' => 'Throttled',
            'email' => 'throttled@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => 'nope',
        ])->assertStatus(429);
    }
}
