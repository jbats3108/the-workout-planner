<?php

namespace Tests\Unit\Shared\Mail;

use App\Shared\Enums\FeedbackCategory;
use App\Shared\Enums\FormSubmissionType;
use App\Shared\Mail\FormSubmissionReceivedMail;
use App\Shared\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormSubmissionReceivedMailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mail_includes_submission_details(): void
    {
        $submission = FormSubmission::query()->create([
            'type' => FormSubmissionType::Feedback,
            'category' => FeedbackCategory::BugReport,
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'message' => "Two lines\nof feedback",
        ]);

        $mail = new FormSubmissionReceivedMail($submission);

        $mail->assertHasSubject('[OVRLOAD] Feedback (Bug Report) from Sam');
        $mail->assertHasReplyTo('sam@example.com', 'Sam');
        $mail->assertSeeInHtml('Bug Report');
        $mail->assertSeeInHtml('Sam');
        $mail->assertSeeInHtml('sam@example.com');
        $mail->assertSeeInHtml('Two lines');
        $mail->assertSeeInText('Two lines');
    }
}
