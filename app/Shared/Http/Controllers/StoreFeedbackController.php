<?php

namespace App\Shared\Http\Controllers;

use App\Shared\Data\StoreFeedbackData;
use App\Shared\Enums\FormSubmissionType;
use App\Shared\Services\FormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreFeedbackController extends Controller
{
    public function __invoke(
        StoreFeedbackData $data,
        Request $request,
        FormSubmissionService $submissions,
    ): RedirectResponse {
        $submissions->submit(
            FormSubmissionType::Feedback,
            $data->name,
            $data->email,
            $data->message,
            $request,
            $data->category,
        );

        return redirect()
            ->route('feedback')
            ->with('success', 'Thanks — your feedback was sent.');
    }
}
