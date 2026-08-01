<?php

namespace App\Auth\Services;

use App\Auth\Models\RegistrationInvite;
use App\Users\Models\User;
use Illuminate\Support\Str;

class RegistrationInviteService
{
    /** @var list<string> */
    public const array ALLOWED_ROLES = ['user', 'admin'];

    public function isMasterInvite(string $token): bool
    {
        $expected = config('registration.invite');

        return is_string($expected)
            && $expected !== ''
            && hash_equals($expected, $token);
    }

    public function findUsable(string $token): ?RegistrationInvite
    {
        return RegistrationInvite::query()
            ->usable()
            ->where('token', $token)
            ->first();
    }

    public function accepts(string $token): bool
    {
        return $this->isMasterInvite($token) || $this->findUsable($token) !== null;
    }

    /**
     * @return array{role: string, invite: ?RegistrationInvite}
     */
    public function resolve(string $token, bool $forUpdate = false): array
    {
        if ($this->isMasterInvite($token)) {
            $role = config('registration.invite_role', 'admin');
            $role = is_string($role) && $role !== '' ? $role : 'admin';

            return [
                'role' => $this->assertAllowedRole($role),
                'invite' => null,
            ];
        }

        $query = RegistrationInvite::query()
            ->usable()
            ->where('token', $token);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $invite = $query->first();
        if ($invite === null) {
            abort(404);
        }

        return [
            'role' => $this->assertAllowedRole($invite->role),
            'invite' => $invite,
        ];
    }

    public function assertAllowedRole(string $role): string
    {
        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            abort(404);
        }

        return $role;
    }

    public function consume(?RegistrationInvite $invite, User $user): void
    {
        if ($invite === null) {
            return;
        }

        $claimed = RegistrationInvite::query()
            ->whereKey($invite->id)
            ->usable()
            ->update([
                'used_at' => now(),
                'used_by' => $user->id,
            ]);

        if ($claimed === 0) {
            abort(404);
        }
    }

    public function create(
        User $creator,
        string $role = 'user',
        ?string $note = null,
        ?int $expiresInDays = 7,
    ): RegistrationInvite {
        return RegistrationInvite::create([
            'token' => Str::random(48),
            'created_by' => $creator->id,
            'role' => $this->assertAllowedRole($role),
            'note' => $note,
            'expires_at' => $expiresInDays !== null ? now()->addDays($expiresInDays) : null,
        ]);
    }

    public function revoke(RegistrationInvite $invite): void
    {
        if ($invite->revoked_at !== null || $invite->used_at !== null) {
            return;
        }

        $invite->update(['revoked_at' => now()]);
    }

    public function registrationUrl(string $token): string
    {
        return url('/register?invite='.urlencode($token));
    }
}
