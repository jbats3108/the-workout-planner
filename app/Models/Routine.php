<?php

namespace App\Models;

use App\Traits\HasName;
use Database\Factories\RoutineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Routine extends Model
{
    /** @use HasFactory<RoutineFactory> */
    use HasFactory, HasName, SoftDeletes;

    protected $fillable = [
        'name',
        'owner_id',
        'routine_type_id',
    ];

    /** @return BelongsToMany<Exercise, $this> */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'routine_exercise', 'routine_id', 'exercise_id');
    }

    /** @return BelongsTo<RoutineType, $this> */
    public function routineType(): BelongsTo
    {
        return $this->belongsTo(RoutineType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public static function lookup(string $slug): ?Routine
    {
        return self::firstWhere('slug', $slug);
    }

    protected static function newFactory(): RoutineFactory
    {
        return RoutineFactory::new();
    }
}
