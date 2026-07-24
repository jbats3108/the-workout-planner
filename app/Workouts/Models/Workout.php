<?php

namespace App\Workouts\Models;

use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use App\Routines\Models\Routine;
use App\Users\Models\User;
use Database\Factories\Workouts\WorkoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends Model
{
    /** @use HasFactory<WorkoutFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'routine_id',
        'mode',
        'status',
        'notes',
        'started_at',
        'finished_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mode' => WorkoutMode::class,
            'status' => WorkoutStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Routine, $this> */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /** @return HasMany<WorkoutBlock, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(WorkoutBlock::class)->orderBy('position');
    }

    protected static function newFactory(): WorkoutFactory
    {
        return WorkoutFactory::new();
    }
}
