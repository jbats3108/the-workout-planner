<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domain mailboxes
    |--------------------------------------------------------------------------
    |
    | Public form notifications and general contact. Prod defaults assume
    | Resend receiving on ovr-load.co.uk.
    |
    */

    'mailboxes' => [
        'admin' => env('OVRLOAD_ADMIN_MAILBOX', 'admin@ovr-load.co.uk'),
        'invite' => env('OVRLOAD_INVITE_MAILBOX', 'invite@ovr-load.co.uk'),
        'feedback' => env('OVRLOAD_FEEDBACK_MAILBOX', 'feedback@ovr-load.co.uk'),
    ],

];
