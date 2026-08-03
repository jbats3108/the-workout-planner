<?php

namespace App\Workouts\Models;

use App\Exercises\Enums\ExerciseEquipment;
use App\Exercises\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class WorkoutBlockExercise extends Model
{
    #[Override]
    protected $fillable = [
        'workout_block_id',
        'exercise_id',
        'position',
        'exercise_name',
        'equipment',
        'working_weight_g',
        'prescribed_reps',
        'achievement_floor',
        'progression_target',
    ];

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'equipment' => ExerciseEquipment::class,
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
