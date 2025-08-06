<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutType extends Model
{
    use HasFactory, SoftDeletes, HasName, HasSlug;

    protected $fillable = [
        'name',
        'slug',
    ];
}
