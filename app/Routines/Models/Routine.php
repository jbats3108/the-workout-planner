<?php

namespace App\Routines\Models;

use App\Routines\Services\RoutineSlugGenerator;
use App\Shared\Traits\HasName;
use App\Users\Models\User;
use App\Workouts\Models\Workout;
use Database\Factories\RoutineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Routine extends Model
{
    /** @use HasFactory<RoutineFactory> */
    use HasFactory;

    use HasName;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'deload_weight_factor',
        'deload_reps_factor',
    ];

    protected static function booted(): void
    {
        static::creating(function (Routine $routine): void {
            if ($routine->slug !== null && $routine->slug !== '') {
                return;
            }

            $user = $routine->user ?? User::query()->find($routine->user_id);
            if ($user === null) {
                return;
            }

            $routine->slug = RoutineSlugGenerator::forUser($user, (string) $routine->name);
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'deload_weight_factor' => 'decimal:3',
            'deload_reps_factor' => 'decimal:3',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<RoutineBlock, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(RoutineBlock::class)->orderBy('position');
    }

    /** @return HasMany<Workout, $this> */
    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    protected static function newFactory(): RoutineFactory
    {
        return RoutineFactory::new();
    }
}
