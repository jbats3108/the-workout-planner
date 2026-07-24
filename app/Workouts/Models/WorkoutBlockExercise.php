<?php

namespace App\Workouts\Models;

use App\Exercises\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutBlockExercise extends Model
{
    protected $fillable = [
        'workout_block_id',
        'exercise_id',
        'position',
        'exercise_name',
        'working_weight_g',
        'prescribed_reps',
        'achievement_floor',
        'progression_target',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'working_weight_g' => 'integer',
            'prescribed_reps' => 'integer',
            'achievement_floor' => 'integer',
            'progression_target' => 'integer',
        ];
    }

    /** @return BelongsTo<WorkoutBlock, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(WorkoutBlock::class, 'workout_block_id');
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /** @return HasMany<WorkoutSet, $this> */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
