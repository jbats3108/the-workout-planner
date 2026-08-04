<?php

namespace App\Shared\Mail;

use App\Shared\Enums\FormSubmissionType;
use App\Shared\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sync notify for low-volume beta forms. Do not add ShouldQueue without afterCommit.
 */
class FormSubmissionReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly FormSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        /** @var FormSubmissionType $type */
        $type = $this->submission->type;
        $subject = sprintf('[OVRLOAD] %s from %s', $type->label(), $this->submission->name);

        if ($this->submission->category !== null) {
            $subject = sprintf(
                '[OVRLOAD] %s (%s) from %s',
                $type->label(),
                $this->submission->category->label(),
                $this->submission->name,
            );
        }

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address($this->submission->email, $this->submission->name),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.shared.form-submission-received',
            text: 'mail.shared.form-submission-received-text',
        );
    }
}
