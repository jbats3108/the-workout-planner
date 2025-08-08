<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MuscleGroup extends Model
{
    use HasFactory, HasName, HasSlug, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    public static function lookup(string $slug): ?MuscleGroup
    {
        return static::firstWhere('slug', $slug);
    }
}
