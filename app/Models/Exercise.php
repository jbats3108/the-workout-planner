<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use App\Traits\HasName;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use HasFactory, SoftDeletes, HasName, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'primary_muscle_group_id',
        'secondary_muscle_group_id',
        'movement_type',
        'difficulty',
        'equipment',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'difficulty'    => Difficulty::class,
            'equipment'     => 'array',
        ];
    }

    public function primaryMuscleGroup(): BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'primary_muscle_group_id');
    }

    public function secondaryMuscleGroup(): ?BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'secondary_muscle_group_id');
    }

    public function movementType(): MovementType
    {
        return $this->movement_type;
    }

    public function workouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'workout_exercise', 'exercise_id', 'workout_id');
    }

    #[Scope]
    protected function whereMuscleGroup(Builder $query, MuscleGroup $muscleGroup): Builder
    {
        return $query
            ->where('primary_muscle_group_id', $muscleGroup->id)
            ->orWhere('secondary_muscle_group_id', $muscleGroup->id);
    }

    #[Scope]
    protected function whereDifficulty(Builder $query, Difficulty $difficulty): Builder
    {
        return $query->where('difficulty', $difficulty);
    }

    #[Scope]
    protected function whereMovementType(Builder $query, MovementType $movementType): Builder
    {
        return $query->where(['movement_type' => $movementType]);
    }

    #[Scope]
    protected function whereEquipment(Builder $query, string $equipmentSearch): Builder
    {
        return $query->whereJsonContains('equipment', $equipmentSearch);
    }
}
