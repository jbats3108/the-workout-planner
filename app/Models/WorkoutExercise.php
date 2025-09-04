<?php

namespace App\Models;

use Database\Factories\WorkoutExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutExercise extends Model
{
    /** @use HasFactory<WorkoutExerciseFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'workout_id',
        'routine_exercise_id',
    ];

    /**
     * @return BelongsTo<Workout, $this>
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /**
     * @return BelongsTo<RoutineExercise, $this>
     */
    public function routineExercise(): BelongsTo
    {
        return $this->belongsTo(RoutineExercise::class);
    }

    public function exercise(): Exercise
    {
        return $this->routineExercise->exercise;
    }

    public function primaryMuscleGroup(): MuscleGroup
    {
        return $this->exercise()->primaryMuscleGroup;
    }

    public function secondaryMuscleGroup(): ?MuscleGroup
    {
        return $this->exercise()->secondaryMuscleGroup;
    }
}
