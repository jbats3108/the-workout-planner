<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlateProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PlateProfileBar, $this> */
    public function bars(): HasMany
    {
        return $this->hasMany(PlateProfileBar::class);
    }

    /** @return HasMany<PlateProfilePlate, $this> */
    public function plates(): HasMany
    {
        return $this->hasMany(PlateProfilePlate::class);
    }
}
