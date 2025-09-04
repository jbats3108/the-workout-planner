<?php

namespace App\Models\Workouts;

use App\Models\Routine;
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
        'routine_id',
        'notes',
    ];

    protected static function newFactory(): WorkoutFactory
    {
        return new WorkoutFactory;
    }

    /**
     * @return BelongsTo<Routine, $this>
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /**
     * @return HasMany<WorkoutExercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    public function addNotes(string $note): void
    {
        $this->update([
            'notes' => $note,
        ]);
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
