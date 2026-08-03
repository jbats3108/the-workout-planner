<?php

namespace App\Admin\Http\Controllers;

use App\Auth\Models\RegistrationInvite;
use App\Auth\Services\RegistrationInviteService;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ResendAdminInviteController extends Controller
{
    public function __invoke(RegistrationInvite $invite, RegistrationInviteService $invites): RedirectResponse
    {
        if (! $invite->isUsable() || $invite->email === null || $invite->email === '') {
            return redirect()
                ->route('admin.invites')
                ->with('error', 'This invite cannot be resent.');
        }

        try {
            $invites->send($invite);
        } catch (Throwable) {
            return redirect()
                ->route('admin.invites')
                ->with('error', 'Could not resend invite email. Try again.');
        }

        return redirect()
            ->route('admin.invites')
            ->with('success', 'Invite resent to '.$invite->email.'.')
            ->with('invite_url', $invites->registrationUrl($invite->token));
    }
}
