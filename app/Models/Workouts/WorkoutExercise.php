<?php

namespace App\Models\Workouts;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\RoutineExercise;
use Database\Factories\Workouts\WorkoutExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * @return HasMany<WorkoutSet, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
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
