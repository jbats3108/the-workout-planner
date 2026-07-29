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
