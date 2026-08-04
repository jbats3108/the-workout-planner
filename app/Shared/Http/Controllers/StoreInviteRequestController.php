<?php

namespace App\Shared\Http\Controllers;

use App\Shared\Data\StoreInviteRequestData;
use App\Shared\Enums\FormSubmissionType;
use App\Shared\Services\FormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreInviteRequestController extends Controller
{
    public function __invoke(
        StoreInviteRequestData $data,
        Request $request,
        FormSubmissionService $submissions,
    ): RedirectResponse {
        $submissions->submit(
            FormSubmissionType::InviteInterest,
            $data->name,
            $data->email,
            $data->message ?? '',
            $request,
        );

        return redirect()
            ->route('invite-request')
            ->with('success', 'Thanks — your invite request was sent. I\'ll email you if I can offer a spot.');
    }
}
