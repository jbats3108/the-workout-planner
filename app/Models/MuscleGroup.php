<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasSlug;
use Database\Factories\MuscleGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MuscleGroup extends Model
{
    /** @use HasFactory<MuscleGroupFactory> */
    use HasFactory;

    use HasName;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function newFactory(): MuscleGroupFactory
    {
        return MuscleGroupFactory::new();
    }
}
