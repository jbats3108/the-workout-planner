<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends Model
{
    use HasFactory, SoftDeletes, HasName, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'workout_type_id',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercise', 'workout_id', 'exercise_id');
    }

    public function workoutType(): BelongsTo
    {
        return $this->belongsTo(WorkoutType::class);
    }
}
