<?php

namespace App\Auth\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sync by design (invite create rolls back if send fails). Do not add ShouldQueue.
 */
class RegistrationInviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $registrationUrl,
        public readonly string $inviterName,
        public readonly string $replyToEmail,
        public readonly string $replyToName,
        public readonly ?CarbonInterface $expiresAt = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re invited to OVRLOAD',
            replyTo: [
                new Address($this->replyToEmail, $this->replyToName),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.auth.registration-invite',
            text: 'mail.auth.registration-invite-text',
        );
    }
}
