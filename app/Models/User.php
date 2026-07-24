<?php

namespace App\Models;

use App\Enums\WeightUnit;
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
        ];
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

    /** @return HasMany<\App\Models\Workouts\Workout, $this> */
    public function workouts(): HasMany
    {
        return $this->hasMany(\App\Models\Workouts\Workout::class);
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
