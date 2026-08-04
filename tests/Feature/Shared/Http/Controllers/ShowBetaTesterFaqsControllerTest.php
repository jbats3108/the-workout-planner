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
}
