<?php

namespace App\Models;

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
    ];

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
}
