<?php

namespace Tests\Unit\Workouts\Models;

use App\Workouts\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class WorkoutRouteBindingTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function owner_resolves_by_ulid(): void
    {
        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $resolved = (new Workout)->resolveRouteBinding($workout->ulid);

        $this->assertTrue($workout->is($resolved));
    }

    #[Test]
    public function other_user_does_not_resolve_by_ulid(): void
    {
        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->secondUser);

        $this->assertNull((new Workout)->resolveRouteBinding($workout->ulid));
    }

    #[Test]
    public function route_urls_use_ulid_not_id(): void
    {
        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $url = route('workouts.play', $workout);

        $this->assertStringContainsString($workout->ulid, $url);
        $this->assertStringNotContainsString('/'.$workout->id.'/', $url);
    }
}
