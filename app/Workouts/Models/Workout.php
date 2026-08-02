<?php

namespace App\Workouts\Models;

use App\Routines\Models\Routine;
use App\Users\Enums\BumpWhen;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use Database\Factories\Workouts\WorkoutFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workout extends Model
{
    /** @use HasFactory<WorkoutFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'routine_id',
        'ulid',
        'mode',
        'bump_when',
        'status',
        'notes',
        'started_at',
        'finished_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Workout $workout): void {
            if ($workout->ulid !== null && $workout->ulid !== '') {
                return;
            }

            $workout->ulid = (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mode' => WorkoutMode::class,
            'bump_when' => BumpWhen::class,
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

    /** @return HasMany<BumpRecord, $this> */
    public function bumpRecords(): HasMany
    {
        return $this->hasMany(BumpRecord::class);
    }

    /** @param  Builder<Workout>  $query */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', WorkoutStatus::Finished);
    }

    public static function latestNonDeloadFinishedForRoutine(User $user, int $routineId): ?self
    {
        return self::query()
            ->where('user_id', $user->id)
            ->where('routine_id', $routineId)
            ->finished()
            ->where('mode', '!=', WorkoutMode::Deload)
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->first();
    }

    public function isEligibleForProgressionReEval(): bool
    {
        if ($this->status !== WorkoutStatus::Finished || $this->mode === WorkoutMode::Deload) {
            return false;
        }

        $latest = self::latestNonDeloadFinishedForRoutine($this->user, $this->routine_id);

        return $latest?->id === $this->id;
    }

    protected static function newFactory(): WorkoutFactory
    {
        return WorkoutFactory::new();
    }
}
