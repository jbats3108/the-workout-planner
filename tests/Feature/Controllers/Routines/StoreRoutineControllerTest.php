<?php

namespace Tests\Feature\Controllers\Routines;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_must_have_a_name(): void
    {
        // When
        $response = $this->actingAs($this->user)->post('/routines/create', []);

        // Then
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function it_creates_a_new_routine(): void
    {
        // Given
        $createRoutineRequest = [
            'name' => 'Test Routine',
        ];

        // When
        $response = $this->actingAs($this->user)->post('/routines/create', $createRoutineRequest);

        // Then
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('routines', [
            'name' => 'Test Routine',
            'user_id' => $this->user->id,
        ]);
    }
}
