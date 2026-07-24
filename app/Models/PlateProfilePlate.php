<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlateProfilePlate extends Model
{
    protected $fillable = [
        'plate_profile_id',
        'denomination_g',
        'count',
        'colour',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'denomination_g' => 'integer',
            'count' => 'integer',
        ];
    }

    /** @return BelongsTo<PlateProfile, $this> */
    public function plateProfile(): BelongsTo
    {
        return $this->belongsTo(PlateProfile::class);
    }
}
