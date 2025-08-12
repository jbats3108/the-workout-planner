<?php

namespace Feature\Controllers\Routines;

use App\Models\RoutineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class CreateRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_must_have_a_name_and_a_slug(): void
    {
        // Given
        $routineType = RoutineType::factory()->create();

        $createRoutineRequest = [
            'routine_type' => $routineType->getSlug(),
        ];

        // When
        $response = $this->actingAs($this->user)->post('/routines/create', $createRoutineRequest);

        // Then
        $response->assertSessionHasErrors('name');
        $response->assertSessionHasErrors('slug');

        $response->assertSessionDoesntHaveErrors(['routine_type']);

    }

    #[Test]
    public function it_rejects_requests_with_an_invalid_routine_type(): void
    {
        // Given
        $invalidRoutineSlug = 'invalid-routine-type';

        $createRoutineRequest = [
            'name' => 'Test Routine',
            'slug' => 'test-routine',
            'routine_type' => $invalidRoutineSlug,
        ];

        // When
        $response = $this->actingAs($this->user)->post('/routines/create', $createRoutineRequest);

        // Then
        $response->assertSessionHasErrors('routine_type');

        $response->assertSessionDoesntHaveErrors(['name', 'slug']);

    }

    #[Test]
    public function it_creates_a_new_routine(): void
    {
        // Given
        $routineType = RoutineType::factory()->create();

        $createRoutineRequest = [
            'name' => 'Test Routine',
            'slug' => 'test-routine',
            'routine_type' => $routineType->getSlug(),
        ];

        // When
        $response = $this->actingAs($this->user)->post('/routines/create', $createRoutineRequest);

        // Then
        $response->assertCreated();

        $this->assertDatabaseHas('routines', [
            'name' => 'Test Routine',
            'slug' => 'test-routine',
            'routine_type_id' => $routineType->id,
            'owner_id' => $this->user->id,
        ]);

    }
}
