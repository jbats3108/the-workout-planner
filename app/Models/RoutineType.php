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
    /** @use HasFactory<RoutineTypeFactory> */
    use HasFactory, HasName, HasSlug, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];
}
