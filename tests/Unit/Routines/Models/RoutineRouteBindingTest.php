<?php

namespace Tests\Unit\Routines\Models;

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
        $this->seedUsers();
    }

    #[Test]
    public function owner_resolves_by_slug(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-owner',
        ]);

        $this->actingAs($this->user);

        $resolved = (new Routine)->resolveRouteBinding('route-binding-owner');

        $this->assertTrue($routine->is($resolved));
    }

    #[Test]
    public function other_user_does_not_resolve_by_slug(): void
    {
        Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-hidden',
        ]);

        $this->actingAs($this->secondUser);

        $this->assertNull((new Routine)->resolveRouteBinding('route-binding-hidden'));
    }

    #[Test]
    public function admin_resolves_any_users_routine_by_slug(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-admin',
        ]);

        $this->actingAs($this->adminUser);

        $resolved = (new Routine)->resolveRouteBinding('route-binding-admin');

        $this->assertTrue($routine->is($resolved));
    }

    #[Test]
    public function route_urls_use_slug_not_id(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create([
            'slug' => 'route-binding-url',
        ]);

        $this->assertStringContainsString('route-binding-url', route('routines.edit', $routine));
        $this->assertStringNotContainsString('/'.$routine->id.'/', route('routines.edit', $routine));
    }
}
