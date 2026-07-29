<?php

declare(strict_types=1);

namespace App\Routines\Services;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use Illuminate\Support\Str;

final class RoutineSlugGenerator
{
    public static function forUser(User $user, string $name, ?int $ignoreRoutineId = null): string
    {
        $base = Str::slug($name) ?: 'routine';
        $slug = $base;
        $suffix = 2;

        while (self::existsForUser($user, $slug, $ignoreRoutineId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function existsForUser(User $user, string $slug, ?int $ignoreRoutineId): bool
    {
        $query = Routine::withTrashed()
            ->where('user_id', $user->id)
            ->where('slug', $slug);

        if ($ignoreRoutineId !== null) {
            $query->whereKeyNot($ignoreRoutineId);
        }

        return $query->exists();
    }
}
