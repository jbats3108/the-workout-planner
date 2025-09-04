<?php

namespace App\Models\Workouts;

use Database\Factories\Workouts\WorkoutSetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutSet extends Model
{
    /** @use HasFactory<WorkoutSetFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'workout_exercise_id',
        'set',
        'reps',
        'weight',
    ];

    /**
     * @return BelongsTo<WorkoutExercise, $this>
     */
    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }

    public function recordReps(int $reps): void
    {
        $this->update(['reps' => $reps]);
    }

    public function recordWeight(float $weight): void
    {
        $this->update(['weight' => $weight]);
    }
}
