<?php

namespace App\Models;

use Database\Factories\RoutineExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExercise extends Model
{
    /** @use HasFactory<RoutineExerciseFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'routine_exercise';

    protected $fillable = [
        'exercise_id',
        'routine_id',
        'sets',
        'reps',
        'weight',
        'to_failure',
    ];

    protected function casts(): array
    {
        return [
            'to_failure' => 'boolean',
        ];
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /** @return BelongsTo<Routine, $this> */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
