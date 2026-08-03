<?php

namespace App\Admin\Http\Controllers;

use App\Auth\Models\RegistrationInvite;
use App\Auth\Services\RegistrationInviteService;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAdminInvitesController extends Controller
{
    public function __invoke(Request $request, RegistrationInviteService $invites): Response
    {
        $rows = RegistrationInvite::query()
            ->with(['creator:id,name', 'usedByUser:id,name,email'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (RegistrationInvite $invite): array => [
                'id' => $invite->id,
                'note' => $invite->note,
                'email' => $invite->email,
                'role' => $invite->role,
                'url' => $invites->registrationUrl($invite->token),
                'created_by' => $invite->creator?->name,
                'created_at' => $invite->created_at?->toDateTimeString(),
                'expires_at' => $invite->expires_at?->toDateTimeString(),
                'used_at' => $invite->used_at?->toDateTimeString(),
                'used_by' => $invite->usedByUser?->email,
                'revoked_at' => $invite->revoked_at?->toDateTimeString(),
                'usable' => $invite->isUsable(),
            ]);

        return Inertia::render('admin/Invites', [
            'invites' => $rows,
            'master_enabled' => is_string(config('registration.invite')) && config('registration.invite') !== '',
        ]);
    }
}
