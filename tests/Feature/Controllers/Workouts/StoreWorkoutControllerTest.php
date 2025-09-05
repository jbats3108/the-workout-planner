<?php

namespace Tests\Feature\Controllers\Workouts;

use App\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreWorkoutControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_requires_the_routine_to_belong_to_the_user(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $route = route('workout.store', ['routine' => $routine->id]);
        $response = $this->actingAs($this->secondUser)->post($route);

        // Then
        $response->assertForbidden();

    }
}
