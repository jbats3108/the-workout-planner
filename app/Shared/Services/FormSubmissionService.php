<?php

namespace App\Shared\Services;

use App\Shared\Enums\FeedbackCategory;
use App\Shared\Enums\FormSubmissionType;
use App\Shared\Mail\FormSubmissionReceivedMail;
use App\Shared\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FormSubmissionService
{
    /**
     * Persist the submission and email the matching mailbox.
     * Honeypot hits return null without storing.
     * Mail failures are logged; the row is kept.
     */
    public function submit(
        FormSubmissionType $type,
        string $name,
        string $email,
        string $message,
        Request $request,
        ?FeedbackCategory $category = null,
    ): ?FormSubmission {
        if ($this->isHoneypotTriggered($request)) {
            return null;
        }

        $submission = FormSubmission::query()->create([
            'type' => $type,
            'category' => $category,
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'ip_address' => $request->ip(),
            'user_agent' => $this->truncateUserAgent($request->userAgent()),
        ]);

        $this->notifyMailbox($submission);

        return $submission;
    }

    public function recipientAddress(FormSubmissionType $type): string
    {
        $address = config('ovrload.mailboxes.'.$type->mailboxKey());

        if (! is_string($address) || $address === '') {
            $fallback = config('ovrload.mailboxes.admin');

            return is_string($fallback) && $fallback !== ''
                ? $fallback
                : (string) config('mail.from.address');
        }

        return $address;
    }

    private function notifyMailbox(FormSubmission $submission): void
    {
        /** @var FormSubmissionType $type */
        $type = $submission->type;

        try {
            Mail::to($this->recipientAddress($type))->send(new FormSubmissionReceivedMail($submission));
        } catch (Throwable $e) {
            Log::error('Form submission notification failed', [
                'submission_id' => $submission->id,
                'type' => $type->value,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function isHoneypotTriggered(Request $request): bool
    {
        $honeypot = $request->input('website');

        return is_string($honeypot) && $honeypot !== '';
    }

    private function truncateUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 2000);
    }
}
