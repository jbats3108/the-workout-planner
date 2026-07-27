<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master registration invite (optional)
    |--------------------------------------------------------------------------
    |
    | Bootstrap / emergency secret. When set, /register?invite=THIS works and
    | assigns invite_role. Leave empty locally. Prefer Admin → Invites for
    | one-time links. Generate with: php artisan registration:invite-secret
    |
    */
    'invite' => env('REGISTRATION_INVITE'),

    /*
    |--------------------------------------------------------------------------
    | Role for master-invite registrations
    |--------------------------------------------------------------------------
    */
    'invite_role' => env('REGISTRATION_INVITE_ROLE', 'admin'),
];
