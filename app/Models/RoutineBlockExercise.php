<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineBlockExercise extends Model
{
    protected $fillable = [
        'routine_block_id',
        'exercise_id',
        'position',
        'working_weight_g',
        'prescribed_reps',
        'achievement_floor_override',
        'progression_target_override',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'working_weight_g' => 'integer',
            'prescribed_reps' => 'integer',
            'achievement_floor_override' => 'integer',
            'progression_target_override' => 'integer',
        ];
    }

    /** @return BelongsTo<RoutineBlock, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(RoutineBlock::class, 'routine_block_id');
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
