<?php

namespace Tests\Feature\Controllers\Routines;

use App\DataTransferObjects\Routines\RoutineData;
use App\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class IndexRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_shows_an_admin_all_routines(): void
    {
        // Given
        $userRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->user->id,
        ]);

        $secondUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->secondUser->id,
        ]);

        // When
        $response = $this->actingAs($this->adminUser)->get(route('routines.index'));

        // Then
        $response->assertOk();

        $userRoutines->each(fn (Routine $routine) => $this->assertContains(RoutineData::fromRoutine($routine)->toArray(), $response->json()));

        $secondUserRoutines->each(fn (Routine $routine) => $this->assertContains(RoutineData::fromRoutine($routine)->toArray(), $response->json()));

    }

    #[Test]
    public function it_only_shows_a_user_their_routines(): void
    {
        // Given
        $userRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->user->id,
        ]);

        $secondUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->secondUser->id,
        ]);

        // When
        $response = $this->actingAs($this->user)->get(route('routines.index'));

        // Then
        $response->assertOk();

        $userRoutines->each(fn (Routine $routine) => $this->assertContains(RoutineData::fromRoutine($routine)->toArray(), $response->json()));

        $secondUserRoutines->each(fn (Routine $routine) => $this->assertNotContains(RoutineData::fromRoutine($routine)->toArray(), $response->json()));

    }
}
