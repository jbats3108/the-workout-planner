<?php

namespace Tests\Feature\Shared\Http\Controllers;

use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowPrivacyControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_can_view_the_privacy_page(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('Privacy'));
    }

    #[Test]
    public function authenticated_users_can_view_the_privacy_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('privacy'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->component('Privacy'));
    }
}
