<?php

namespace App\Admin\Http\Controllers;

use App\Auth\Models\RegistrationInvite;
use App\Auth\Services\RegistrationInviteService;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RevokeAdminInviteController extends Controller
{
    public function __invoke(RegistrationInvite $invite, RegistrationInviteService $invites): RedirectResponse
    {
        $invites->revoke($invite);

        return redirect()
            ->route('admin.invites')
            ->with('success', 'Invite revoked.');
    }
}
