<?php

namespace Tests\Unit\Auth\Mail;

use App\Auth\Mail\RegistrationInviteMail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationInviteMailTest extends TestCase
{
    #[Test]
    public function mail_includes_inviter_link_and_brand(): void
    {
        $mail = new RegistrationInviteMail(
            registrationUrl: 'https://ovrload.test/register?invite=abc',
            inviterName: 'Jamie',
            replyToEmail: 'jamie@example.com',
            replyToName: 'Jamie',
            expiresAt: now()->addDays(7),
        );

        $mail->assertHasSubject('You\'re invited to OVRLOAD');
        $mail->assertSeeInHtml('Jamie invited you to OVRLOAD');
        $mail->assertSeeInHtml('https://ovrload.test/register?invite=abc');
        $mail->assertSeeInHtml('OVR');
        $mail->assertSeeInHtml('LOAD');
        $mail->assertSeeInText('Jamie invited you to OVRLOAD');
    }
}
