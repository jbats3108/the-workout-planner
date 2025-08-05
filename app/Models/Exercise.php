<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'primary_muscle_group_id',
        'secondary_muscle_group_id',
        'movement_type',
        'difficulty'
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'difficulty'    => Difficulty::class,
        ];
    }

    public function getName(): string

    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
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

    public function difficulty(): Difficulty
    {
        return $this->difficulty;
    }
}
