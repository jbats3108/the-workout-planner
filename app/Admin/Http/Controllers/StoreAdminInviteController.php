<?php

namespace App\Admin\Http\Controllers;

use App\Auth\Data\StoreRegistrationInviteData;
use App\Auth\Services\RegistrationInviteService;
use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use Illuminate\Http\RedirectResponse;
use Throwable;

class StoreAdminInviteController extends Controller
{
    public function __invoke(StoreRegistrationInviteData $data, RegistrationInviteService $invites): RedirectResponse
    {
        /** @var User $user */
        $user = request()->user();

        try {
            $invite = $invites->createAndSend(
                $user,
                $data->email,
                $data->role,
                $data->note,
                $data->expiresInDays,
            );
        } catch (Throwable) {
            return redirect()
                ->route('admin.invites')
                ->withInput()
                ->with('error', 'Could not send invite email. Nothing was saved — try again.');
        }

        return redirect()
            ->route('admin.invites')
            ->with('success', 'Invite sent to '.$invite->email.'.')
            ->with('invite_url', $invites->registrationUrl($invite->token));
    }
}
