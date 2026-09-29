<?php

namespace Tests\Feature\Routines\Http\Controllers;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\Routines\Models\Routine;
use Database\Seeders\ExerciseProfileSeeder;
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
        $this->seedUsers(false);
        $this->seed(ExerciseProfileSeeder::class);
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
            'default_exercise_profile_id' => $this->strengthProfile()->id,
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
    public function it_seeds_deload_every_n_from_user_training_defaults(): void
    {
        $this->user->update([
            'deload_every_n_default' => 4,
        ]);

        $this->actingAs($this->user)->post(route('routines.store'), [
            'name' => 'From Defaults',
            'default_exercise_profile_id' => $this->strengthProfile()->id,
        ])->assertRedirect();

        $routine = Routine::query()->where('name', 'From Defaults')->first();
        $this->assertNotNull($routine);
        $this->assertSame(4, $routine->deload_every_n);
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
    public function it_rejects_out_of_range_deload_every_n(): void
    {
        $before = Routine::query()->count();

        $this->actingAs($this->user)->post(route('routines.store'), [
            'name' => 'Test Routine',
            'deload_every_n' => 100,
        ])->assertSessionHasErrors('deload_every_n');

        $this->assertSame($before, Routine::query()->count());
    }

    private function strengthProfile(): ExerciseProfile
    {
        return ExerciseProfile::query()->where('slug', 'preset-strength')->firstOrFail();
    }
}
