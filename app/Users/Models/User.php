<?php

namespace App\Users\Models;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Users\Enums\WeightUnit;
use App\Workouts\Models\Workout;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'weight_unit',
        'achievement_floor_default',
        'progression_target_default',
        'warm_up_steps_default',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'weight_unit' => WeightUnit::class,
            'achievement_floor_default' => 'integer',
            'progression_target_default' => 'integer',
            'warm_up_steps_default' => 'array',
        ];
    }

    /**
     * App-wide warm-up ladder when the user has not set prefs yet.
     *
     * @return list<array{percent: int, reps: int}>
     */
    public static function fallbackWarmUpSteps(): array
    {
        return [
            ['percent' => 40, 'reps' => 5],
            ['percent' => 60, 'reps' => 3],
            ['percent' => 80, 'reps' => 1],
        ];
    }

    /**
     * Steps to seed into new routine blocks. Null column → app fallback; empty list → no warm-up.
     *
     * @return list<array{percent: int, reps: int}>
     */
    public function resolvedWarmUpStepsDefault(): array
    {
        if ($this->warm_up_steps_default === null) {
            return self::fallbackWarmUpSteps();
        }

        return array_values(array_map(
            static fn (mixed $step): array => [
                'percent' => (int) (is_array($step) ? ($step['percent'] ?? 0) : 0),
                'reps' => (int) (is_array($step) ? ($step['reps'] ?? 5) : 5),
            ],
            $this->warm_up_steps_default
        ));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /** @return HasMany<Routine, $this> */
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }

    /** @return HasMany<Exercise, $this> */
    public function customExercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    /** @return HasMany<Workout, $this> */
    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    /** @return HasOne<PlateProfile, $this> */
    public function plateProfile(): HasOne
    {
        return $this->hasOne(PlateProfile::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
