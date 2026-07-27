<?php

namespace App\Admin\Http\Controllers;

use App\Auth\Services\RegistrationInviteService;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreAdminInviteController extends Controller
{
    public function __invoke(Request $request, RegistrationInviteService $invites): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['user', 'admin'])],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $invite = $invites->create(
            $request->user(),
            $data['role'],
            $data['note'] ?? null,
            array_key_exists('expires_in_days', $data) ? $data['expires_in_days'] : 7,
        );

        return redirect()
            ->route('admin.invites')
            ->with('success', 'Invite created.')
            ->with('invite_url', $invites->registrationUrl($invite->token));
    }
}
