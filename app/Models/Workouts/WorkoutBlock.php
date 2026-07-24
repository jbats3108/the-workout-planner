<?php

namespace App\Models\Workouts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkoutBlock extends Model
{
    protected $fillable = [
        'workout_id',
        'position',
        'is_superset',
        'has_setup_after',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_superset' => 'boolean',
            'has_setup_after' => 'boolean',
        ];
    }

    /** @return BelongsTo<Workout, $this> */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /** @return HasMany<WorkoutBlockExercise, $this> */
    public function blockExercises(): HasMany
    {
        return $this->hasMany(WorkoutBlockExercise::class)->orderBy('position');
    }

    /** @return HasMany<WorkoutSetGroup, $this> */
    public function setGroups(): HasMany
    {
        return $this->hasMany(WorkoutSetGroup::class);
    }

    /** @return HasOne<WorkoutSetGroup, $this> */
    public function warmUpSetGroup(): HasOne
    {
        return $this->hasOne(WorkoutSetGroup::class)->where('type', 'warm_up');
    }

    /** @return HasOne<WorkoutSetGroup, $this> */
    public function workingSetGroup(): HasOne
    {
        return $this->hasOne(WorkoutSetGroup::class)->where('type', 'working');
    }
}
