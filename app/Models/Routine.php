<?php

namespace App\Models;

use App\Traits\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Routine extends Model
{
    use HasFactory, HasName, SoftDeletes;

    protected $fillable = [
        'name',
        'owner_id',
        'routine_type_id',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'routine_exercise', 'routine_id', 'exercise_id');
    }

    public function routineType(): BelongsTo
    {
        return $this->belongsTo(RoutineType::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
