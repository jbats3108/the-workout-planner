<?php

namespace Tests\Feature\Routines\Http\Controllers;

use App\Routines\Models\Routine;
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
        $response = $this->actingAs($this->user)->post(route('routines.store'), []);

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
        $response = $this->actingAs($this->user)->post(route('routines.store'), $createRoutineRequest);

        // Then
        $routine = Routine::where('name', 'Test Routine')->first();
        $this->assertNotNull($routine);
        $this->assertSame('test-routine', $routine->slug);
        $response->assertRedirect(route('routines.edit', $routine));
        $this->assertStringContainsString('test-routine', $response->headers->get('Location') ?? '');

        $this->assertDatabaseHas('routines', [
            'name' => 'Test Routine',
            'slug' => 'test-routine',
            'user_id' => $this->user->id,
        ]);
    }
}
