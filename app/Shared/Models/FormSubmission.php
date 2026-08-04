<?php

namespace App\Shared\Models;

use App\Shared\Enums\FeedbackCategory;
use App\Shared\Enums\FormSubmissionType;
use Illuminate\Database\Eloquent\Model;
use Override;

class FormSubmission extends Model
{
    #[Override]
    protected $fillable = [
        'type',
        'category',
        'name',
        'email',
        'message',
        'ip_address',
        'user_agent',
    ];

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => FormSubmissionType::class,
            'category' => FeedbackCategory::class,
        ];
    }
}
