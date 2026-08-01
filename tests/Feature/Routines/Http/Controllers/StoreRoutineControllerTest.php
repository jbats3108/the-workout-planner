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

    #[Test]
    public function it_rejects_an_overlong_name(): void
    {
        $before = Routine::query()->count();

        $response = $this->actingAs($this->user)->post(route('routines.store'), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame($before, Routine::query()->count());
    }

    #[Test]
    public function it_rejects_out_of_range_deload_factors(): void
    {
        $before = Routine::query()->count();

        $this->actingAs($this->user)->post(route('routines.store'), [
            'name' => 'Test Routine',
            'deload_weight_factor' => 5.1,
        ])->assertSessionHasErrors('deload_weight_factor');

        $this->actingAs($this->user)->post(route('routines.store'), [
            'name' => 'Test Routine',
            'deload_reps_factor' => 10.1,
        ])->assertSessionHasErrors('deload_reps_factor');

        $this->assertSame($before, Routine::query()->count());
    }
}
