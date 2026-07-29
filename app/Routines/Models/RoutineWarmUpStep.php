<?php

namespace App\Routines\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineWarmUpStep extends Model
{
    protected $fillable = [
        'routine_set_group_id',
        'position',
        'percent_of_working',
        'reps',
        'has_setup_after',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'percent_of_working' => 'integer',
            'reps' => 'integer',
            'has_setup_after' => 'boolean',
        ];
    }

    /** @return BelongsTo<RoutineSetGroup, $this> */
    public function setGroup(): BelongsTo
    {
        return $this->belongsTo(RoutineSetGroup::class, 'routine_set_group_id');
    }
}
