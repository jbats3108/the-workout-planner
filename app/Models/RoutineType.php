<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasSlug;
use Database\Factories\RoutineTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutineType extends Model
{
    use HasFactory;
    use HasName;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function newFactory(): RoutineTypeFactory
    {
        return RoutineTypeFactory::new();
    }
}
