<?php

namespace Tests\Feature\Shared\Http\Controllers;

use App\Shared\Enums\FeedbackCategory;
use App\Shared\Enums\FormSubmissionType;
use App\Shared\Mail\FormSubmissionReceivedMail;
use App\Shared\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_can_view_invite_request_and_feedback_forms(): void
    {
        $this->get(route('invite-request'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('InviteRequest'));

        $this->get(route('feedback'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('Feedback'));
    }

    #[Test]
    public function invite_request_is_stored_and_mailed_to_invite_mailbox(): void
    {
        Mail::fake();
        config(['ovrload.mailboxes.invite' => 'invite@ovr-load.co.uk']);

        $this->post(route('invite-request.store'), [
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'message' => 'Looking to beta test',
            'website' => '',
        ])
            ->assertRedirect(route('invite-request'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('form_submissions', [
            'type' => FormSubmissionType::InviteInterest->value,
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'message' => 'Looking to beta test',
        ]);

        Mail::assertSent(
            FormSubmissionReceivedMail::class,
            fn (FormSubmissionReceivedMail $mail): bool => $mail->hasTo('invite@ovr-load.co.uk')
                && $mail->hasReplyTo('alex@example.com', 'Alex'),
        );
    }

    #[Test]
    public function feedback_is_stored_and_mailed_to_feedback_mailbox(): void
    {
        Mail::fake();
        config(['ovrload.mailboxes.feedback' => 'feedback@ovr-load.co.uk']);

        $this->post(route('feedback.store'), [
            'category' => FeedbackCategory::FeatureRequest->value,
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'message' => 'Love the rest timer',
        ])
            ->assertRedirect(route('feedback'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('form_submissions', [
            'type' => FormSubmissionType::Feedback->value,
            'category' => FeedbackCategory::FeatureRequest->value,
            'email' => 'sam@example.com',
        ]);

        Mail::assertSent(
            FormSubmissionReceivedMail::class,
            fn (FormSubmissionReceivedMail $mail): bool => $mail->hasTo('feedback@ovr-load.co.uk')
                && $mail->hasSubject('[OVRLOAD] Feedback (Feature Request) from Sam'),
        );
    }

    #[Test]
    public function feedback_requires_a_category(): void
    {
        Mail::fake();

        $this->from(route('feedback'))
            ->post(route('feedback.store'), [
                'name' => 'Sam',
                'email' => 'sam@example.com',
                'message' => 'Missing category',
            ])
            ->assertSessionHasErrors('category');

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    #[Test]
    public function invite_request_allows_empty_message(): void
    {
        Mail::fake();

        $this->post(route('invite-request.store'), [
            'name' => 'Alex',
            'email' => 'alex@example.com',
        ])->assertRedirect(route('invite-request'));

        $this->assertSame('', FormSubmission::query()->firstOrFail()->message);
    }

    #[Test]
    public function feedback_requires_a_message(): void
    {
        Mail::fake();

        $this->from(route('feedback'))
            ->post(route('feedback.store'), [
                'category' => FeedbackCategory::BugReport->value,
                'name' => 'Sam',
                'email' => 'sam@example.com',
                'message' => '',
            ])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    #[Test]
    public function honeypot_submissions_are_silently_ignored(): void
    {
        Mail::fake();

        $this->post(route('invite-request.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'spam',
            'website' => 'https://spam.example',
        ])
            ->assertRedirect(route('invite-request'))
            ->assertSessionHas('success');

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    #[Test]
    public function beta_faqs_link_to_in_app_forms_without_tally_props(): void
    {
        $this->get(route('beta-tester-faqs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('BetaTesterFaqs')
                ->missing('interestFormUrl')
                ->missing('feedbackFormUrl'));
    }
}
