<?php

namespace App\Workouts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutWarmUpStep extends Model
{
    protected $fillable = [
        'workout_set_group_id',
        'position',
        'percent_of_working',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'percent_of_working' => 'integer',
        ];
    }

    /** @return BelongsTo<WorkoutSetGroup, $this> */
    public function setGroup(): BelongsTo
    {
        return $this->belongsTo(WorkoutSetGroup::class, 'workout_set_group_id');
    }
}
