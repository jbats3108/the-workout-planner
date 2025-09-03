<?php

namespace App\Models;

use Database\Factories\WorkoutSetFactory;
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
        'workout_id',
        'exercise_id',
        'set',
        'weight',
        'reps',
    ];

    /**
     * @return BelongsTo<Workout, $this>
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function recordWeight(float $weight): void
    {
        $this->update([
            'weight' => $weight,
        ]);
    }

    public function recordSetNumber(int $setNumber): void
    {
        $this->update([
            'set' => $setNumber,
        ]);
    }

    public function recordReps(int $reps): void
    {
        $this->update([
            'reps' => $reps,
        ]);
    }

    protected static function newFactory(): WorkoutSetFactory
    {
        return new WorkoutSetFactory;
    }
}
