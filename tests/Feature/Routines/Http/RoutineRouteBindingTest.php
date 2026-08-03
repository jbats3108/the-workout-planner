<?php

namespace Tests\Feature\Routines\Http;

use App\Routines\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class RoutineRouteBindingTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers(false);
    }

    #[Test]
    public function owner_can_open_routine_by_slug(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-owner',
        ]);

        $this->actingAs($this->user)
            ->get(route('routines.edit', ['routine' => 'route-binding-owner']))
            ->assertOk();

        $this->assertStringContainsString('route-binding-owner', route('routines.edit', $routine));
        $this->assertStringNotContainsString('/'.$routine->id.'/', route('routines.edit', $routine));
    }

    #[Test]
    public function other_user_does_not_resolve_another_users_slug(): void
    {
        Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-hidden',
        ]);

        $this->actingAs($this->secondUser)
            ->get(route('routines.show', ['routine' => 'route-binding-hidden']))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Routine not found. Check the URL and try again.');
    }

    #[Test]
    public function admin_can_open_any_users_routine_by_slug(): void
    {
        Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-admin',
        ]);

        $this->actingAs($this->adminUser)
            ->get(route('routines.edit', ['routine' => 'route-binding-admin']))
            ->assertOk();
    }
}
