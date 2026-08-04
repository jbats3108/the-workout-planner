<?php

namespace Tests\Feature\Shared\Http\Controllers;

use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowBetaTesterFaqsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_can_view_the_beta_tester_faqs_page(): void
    {
        config([
            'ovrload.interest_form_url' => null,
            'ovrload.feedback_form_url' => null,
        ]);

        $this->get(route('beta-tester-faqs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('BetaTesterFaqs')
                ->where('interestFormUrl', null)
                ->where('feedbackFormUrl', null));
    }

    #[Test]
    public function authenticated_users_can_view_the_beta_tester_faqs_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('beta-tester-faqs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('BetaTesterFaqs'));
    }

    #[Test]
    public function the_home_page_renders_for_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('Home'));
    }

    #[Test]
    public function form_urls_come_from_config_when_set(): void
    {
        config([
            'ovrload.interest_form_url' => 'https://tally.so/r/interest-test',
            'ovrload.feedback_form_url' => 'https://tally.so/r/feedback-test',
        ]);

        $this->get(route('beta-tester-faqs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('BetaTesterFaqs')
                ->where('interestFormUrl', 'https://tally.so/r/interest-test')
                ->where('feedbackFormUrl', 'https://tally.so/r/feedback-test'));
    }

    #[Test]
    public function blank_form_urls_are_treated_as_null(): void
    {
        config([
            'ovrload.interest_form_url' => '  ',
            'ovrload.feedback_form_url' => '',
        ]);

        $this->get(route('beta-tester-faqs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('BetaTesterFaqs')
                ->where('interestFormUrl', null)
                ->where('feedbackFormUrl', null));
    }
}
