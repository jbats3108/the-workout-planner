<?php

namespace App\Shared\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ShowBetaTesterFaqsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('BetaTesterFaqs', [
            'interestFormUrl' => $this->nullableUrl(config('ovrload.interest_form_url')),
            'feedbackFormUrl' => $this->nullableUrl(config('ovrload.feedback_form_url')),
        ]);
    }

    private function nullableUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);

        return $url !== '' ? $url : null;
    }
}
