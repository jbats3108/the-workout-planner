<?php

namespace Tests\Feature\Auth;

use App\Auth\Services\RegistrationInviteService;
use App\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_registration_is_closed_without_invite(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/register?invite=nope')->assertNotFound();
    }

    public function test_master_invite_can_register_admin(): void
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

    public function test_one_time_invite_can_register_and_is_consumed(): void
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

    public function test_revoked_invite_is_rejected(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $invite = app(RegistrationInviteService::class)->create($admin);
        app(RegistrationInviteService::class)->revoke($invite);

        $this->get('/register?invite='.$invite->token)->assertNotFound();
    }
}
