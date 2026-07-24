<?php

namespace App\Workouts\Models;

use App\Shared\Enums\SetGroupType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSetGroup extends Model
{
    protected $fillable = [
        'workout_block_id',
        'type',
        'set_count',
        'rest_seconds',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => SetGroupType::class,
            'set_count' => 'integer',
            'rest_seconds' => 'integer',
        ];
    }

    /** @return BelongsTo<WorkoutBlock, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(WorkoutBlock::class, 'workout_block_id');
    }

    /** @return HasMany<WorkoutWarmUpStep, $this> */
    public function warmUpSteps(): HasMany
    {
        return $this->hasMany(WorkoutWarmUpStep::class)->orderBy('position');
    }

    /** @return HasMany<WorkoutSet, $this> */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class)->orderBy('set_index');
    }
}
