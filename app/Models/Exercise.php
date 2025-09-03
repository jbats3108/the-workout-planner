<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use App\Traits\HasName;
use App\Traits\HasSlug;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property MuscleGroup | null $secondaryMuscleGroup
 */
class Exercise extends Model
{
    use HasFactory;
    use HasName;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'primary_muscle_group_id',
        'secondary_muscle_group_id',
        'movement_type',
        'difficulty',
        'equipment',
    ];

    /** @return BelongsTo<MuscleGroup, $this> */
    public function primaryMuscleGroup(): BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'primary_muscle_group_id');
    }

    /**
     * @return BelongsTo<MuscleGroup, $this>|null
     */
    public function secondaryMuscleGroup(): ?BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'secondary_muscle_group_id');
    }

    public function movementType(): MovementType
    {
        return $this->movement_type;
    }

    /** @return BelongsToMany<Routine, $this> */
    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'routine_exercise', 'exercise_id', 'routine_id');
    }

    /**
     * @return array{
     *    movement_type: 'App\\Enums\\MovementType',
     *    difficulty: 'App\\Enums\\Difficulty',
     *    equipment: 'array'
     * }
     */
    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'difficulty' => Difficulty::class,
            'equipment' => 'array',
        ];
    }

    /**
     * @param  Builder<Exercise>  $query
     * @return Builder<Exercise>
     */
    #[Scope]
    protected function whereMuscleGroup(Builder $query, MuscleGroup $muscleGroup): Builder
    {
        return $query
            ->whereBelongsTo($muscleGroup, 'primaryMuscleGroup')
            ->orWhereBelongsTo($muscleGroup, 'secondaryMuscleGroup');
    }

    /**
     * @param  Builder<Exercise>  $query
     * @return Builder<Exercise>
     */
    #[Scope]
    protected function whereDifficulty(Builder $query, Difficulty $difficulty): Builder
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * @param  Builder<Exercise>  $query
     * @return Builder<Exercise>
     */
    #[Scope]
    protected function whereMovementType(Builder $query, MovementType $movementType): Builder
    {
        return $query->where(['movement_type' => $movementType]);
    }

    /**
     * @param  Builder<Exercise>  $query
     * @return Builder<Exercise>
     */
    #[Scope]
    protected function whereEquipment(Builder $query, string $equipmentSearch): Builder
    {
        return $query->whereJsonContains('equipment', $equipmentSearch);
    }

    protected static function newFactory(): ExerciseFactory
    {
        return ExerciseFactory::new();
    }
}
