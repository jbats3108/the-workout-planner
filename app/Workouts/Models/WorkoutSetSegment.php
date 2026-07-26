<?php

namespace App\Workouts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSetSegment extends Model
{
    protected $fillable = [
        'workout_set_id',
        'position',
        'weight_g',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'weight_g' => 'integer',
        ];
    }

    /** @return BelongsTo<WorkoutSet, $this> */
    public function set(): BelongsTo
    {
        return $this->belongsTo(WorkoutSet::class, 'workout_set_id');
    }
}
