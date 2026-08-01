<?php

namespace Tests\Unit\Auth\Services;

use App\Auth\Models\RegistrationInvite;
use App\Auth\Services\RegistrationInviteService;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class RegistrationInviteServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationInviteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RegistrationInviteService::class);
        config(['registration.invite' => null]);
    }

    #[Test]
    public function is_master_invite_matches_configured_secret(): void
    {
        config(['registration.invite' => 'master-secret']);

        $this->assertTrue($this->service->isMasterInvite('master-secret'));
        $this->assertFalse($this->service->isMasterInvite('wrong'));
        $this->assertFalse($this->service->isMasterInvite(''));
    }

    #[Test]
    public function accepts_master_or_usable_invite(): void
    {
        config(['registration.invite' => 'master-secret']);
        $invite = $this->service->create(User::factory()->create());

        $this->assertTrue($this->service->accepts('master-secret'));
        $this->assertTrue($this->service->accepts($invite->token));
        $this->assertFalse($this->service->accepts('nope'));
    }

    #[Test]
    public function resolve_returns_master_role_without_invite_row(): void
    {
        config(['registration.invite' => 'master-secret', 'registration.invite_role' => 'admin']);

        $resolved = $this->service->resolve('master-secret');

        $this->assertSame('admin', $resolved['role']);
        $this->assertNull($resolved['invite']);
    }

    #[Test]
    public function resolve_returns_usable_invite(): void
    {
        $invite = $this->service->create(User::factory()->create(), 'user', 'buddy');

        $resolved = $this->service->resolve($invite->token);

        $this->assertSame('user', $resolved['role']);
        $this->assertTrue($resolved['invite']->is($invite));
    }

    #[Test]
    public function resolve_aborts_for_missing_invite(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->service->resolve('missing');
    }

    #[Test]
    public function find_usable_ignores_expired_invites(): void
    {
        $invite = $this->service->create(User::factory()->create());
        $invite->update(['expires_at' => now()->subMinute()]);

        $this->assertNull($this->service->findUsable($invite->token));
    }

    #[Test]
    public function consume_marks_invite_used(): void
    {
        $invite = $this->service->create(User::factory()->create());
        $user = User::factory()->create();

        $this->service->consume($invite, $user);

        $invite->refresh();
        $this->assertNotNull($invite->used_at);
        $this->assertSame($user->id, $invite->used_by);
    }

    #[Test]
    public function consume_null_invite_is_noop(): void
    {
        $this->service->consume(null, User::factory()->create());

        $this->assertSame(0, RegistrationInvite::query()->whereNotNull('used_at')->count());
    }

    #[Test]
    public function consume_aborts_when_invite_already_used(): void
    {
        $invite = $this->service->create(User::factory()->create());
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->service->consume($invite, $first);

        $this->expectException(NotFoundHttpException::class);
        $this->service->consume($invite, $second);
    }

    #[Test]
    public function resolve_for_update_aborts_after_invite_is_consumed(): void
    {
        $invite = $this->service->create(User::factory()->create());
        $this->service->consume($invite, User::factory()->create());

        $this->expectException(NotFoundHttpException::class);
        $this->service->resolve($invite->token, forUpdate: true);
    }

    #[Test]
    public function create_sets_expiry_and_role(): void
    {
        $creator = User::factory()->create();

        $invite = $this->service->create($creator, 'admin', 'note', 3);

        $this->assertSame('admin', $invite->role);
        $this->assertSame('note', $invite->note);
        $this->assertSame($creator->id, $invite->created_by);
        $this->assertNotNull($invite->expires_at);
        $this->assertTrue($invite->expires_at->isAfter(now()->addDays(2)));
    }

    #[Test]
    public function revoke_is_idempotent(): void
    {
        $invite = $this->service->create(User::factory()->create());

        $this->service->revoke($invite);
        $firstRevokedAt = $invite->fresh()->revoked_at;
        $this->service->revoke($invite->fresh());

        $this->assertNotNull($firstRevokedAt);
        $this->assertTrue($firstRevokedAt->equalTo($invite->fresh()->revoked_at));
    }

    #[Test]
    public function registration_url_includes_invite_query(): void
    {
        $url = $this->service->registrationUrl('abc123');

        $this->assertStringContainsString('/register?invite=abc123', $url);
    }
}
