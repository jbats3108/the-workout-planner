<?php

namespace App\Shared\Enums;

enum FeedbackCategory: string
{
    case BugReport = 'bug_report';
    case FeatureRequest = 'feature_request';
    case GeneralFeedback = 'general_feedback';

    public function label(): string
    {
        return match ($this) {
            self::BugReport => 'Bug Report',
            self::FeatureRequest => 'Feature Request',
            self::GeneralFeedback => 'General Feedback',
        };
    }
}
