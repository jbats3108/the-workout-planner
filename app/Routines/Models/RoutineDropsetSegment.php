<?php

namespace App\Routines\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineDropsetSegment extends Model
{
    protected $fillable = [
        'routine_set_group_id',
        'set_index',
        'position',
        'weight_g',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'set_index' => 'integer',
            'position' => 'integer',
            'weight_g' => 'integer',
        ];
    }

    /** @return BelongsTo<RoutineSetGroup, $this> */
    public function setGroup(): BelongsTo
    {
        return $this->belongsTo(RoutineSetGroup::class, 'routine_set_group_id');
    }
}
