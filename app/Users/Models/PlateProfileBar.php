<?php

namespace App\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlateProfileBar extends Model
{
    protected $fillable = [
        'plate_profile_id',
        'name',
        'weight_g',
        'is_default',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'weight_g' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    /** @return BelongsTo<PlateProfile, $this> */
    public function plateProfile(): BelongsTo
    {
        return $this->belongsTo(PlateProfile::class);
    }
}
