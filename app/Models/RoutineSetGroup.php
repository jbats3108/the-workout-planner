<?php

namespace App\Models;

use App\Enums\SetGroupType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineSetGroup extends Model
{
    protected $fillable = [
        'routine_block_id',
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

    /** @return BelongsTo<RoutineBlock, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(RoutineBlock::class, 'routine_block_id');
    }

    /** @return HasMany<RoutineWarmUpStep, $this> */
    public function warmUpSteps(): HasMany
    {
        return $this->hasMany(RoutineWarmUpStep::class)->orderBy('position');
    }
}
