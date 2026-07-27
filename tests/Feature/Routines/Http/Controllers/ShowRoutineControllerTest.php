<?php

namespace Tests\Feature\Routines\Http\Controllers;

use App\Routines\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class ShowRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_redirects_owner_to_the_editor(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->get(route('routines.show', $routine));

        $response->assertRedirect(route('routines.edit', $routine));
    }

    #[Test]
    public function it_allows_an_admin_to_open_show_which_redirects(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->adminUser)->get(route('routines.show', $routine));

        $response->assertRedirect(route('routines.edit', $routine));
    }

    #[Test]
    public function it_does_not_allow_users_to_view_another_users_routine(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->secondUser)->get(route('routines.show', $routine));

        $response
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'You do not have access to that routine.');
    }
}
