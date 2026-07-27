<?php

namespace Tests\Unit\Auth\Models;

use App\Auth\Models\RegistrationInvite;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationInviteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function is_usable_when_fresh(): void
    {
        $invite = $this->makeInvite();

        $this->assertTrue($invite->isUsable());
    }

    #[Test]
    public function is_usable_when_expires_at_is_null(): void
    {
        $invite = $this->makeInvite(['expires_at' => null]);

        $this->assertTrue($invite->isUsable());
    }

    #[Test]
    public function is_not_usable_when_used(): void
    {
        $invite = $this->makeInvite(['used_at' => now()]);

        $this->assertFalse($invite->isUsable());
    }

    #[Test]
    public function is_not_usable_when_revoked(): void
    {
        $invite = $this->makeInvite(['revoked_at' => now()]);

        $this->assertFalse($invite->isUsable());
    }

    #[Test]
    public function is_not_usable_when_expired(): void
    {
        $invite = $this->makeInvite(['expires_at' => now()->subMinute()]);

        $this->assertFalse($invite->isUsable());
    }

    #[Test]
    public function scope_usable_excludes_used_revoked_and_expired(): void
    {
        $fresh = $this->makeInvite(['token' => 'fresh']);
        $this->makeInvite(['token' => 'used', 'used_at' => now()]);
        $this->makeInvite(['token' => 'revoked', 'revoked_at' => now()]);
        $this->makeInvite(['token' => 'expired', 'expires_at' => now()->subDay()]);
        $this->makeInvite(['token' => 'open', 'expires_at' => null]);

        $tokens = RegistrationInvite::query()->usable()->pluck('token')->sort()->values()->all();

        $this->assertSame(['fresh', 'open'], $tokens);
        $this->assertTrue($fresh->isUsable());
    }

    /** @param  array<string, mixed>  $overrides */
    private function makeInvite(array $overrides = []): RegistrationInvite
    {
        $creator = User::factory()->create();

        return RegistrationInvite::create(array_merge([
            'token' => 'tok-'.uniqid(),
            'created_by' => $creator->id,
            'role' => 'user',
            'note' => null,
            'expires_at' => now()->addDays(7),
            'used_at' => null,
            'used_by' => null,
            'revoked_at' => null,
        ], $overrides));
    }
}
