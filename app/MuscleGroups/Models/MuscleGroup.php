<?php

namespace App\MuscleGroups\Models;

use App\Shared\Traits\HasName;
use App\Shared\Traits\HasSlug;
use Database\Factories\MuscleGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

class MuscleGroup extends Model
{
    /** @use HasFactory<MuscleGroupFactory> */
    use HasFactory;

    use HasName;
    use HasSlug;
    use SoftDeletes;

    #[Override]
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function newFactory(): MuscleGroupFactory
    {
        return MuscleGroupFactory::new();
    }
}
