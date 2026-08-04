<?php

namespace App\Shared\Enums;

enum FormSubmissionType: string
{
    case InviteInterest = 'invite_interest';
    case Feedback = 'feedback';

    public function mailboxKey(): string
    {
        return match ($this) {
            self::InviteInterest => 'invite',
            self::Feedback => 'feedback',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::InviteInterest => 'Invite request',
            self::Feedback => 'Feedback',
        };
    }
}
