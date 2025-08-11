<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexRoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $firstUser;

    protected User $secondUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->get();

        $this->firstUser = $users->first();
        $this->secondUser = $users->last();
    }

    #[Test]
    public function it_shows_an_admin_all_routines(): void
    {
        // Given
        $firstUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->firstUser->id,
        ]);

        $secondUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->secondUser->id,
        ]);

        // When
        $response = $this->actingAs($this->adminUser)->get(route('routines.index'));

        // Then
        $response->assertOk();

        $firstUserRoutines->each(function ($routine) use ($response) {
            $response->assertJsonFragment([
                'id' => $routine->id,
                'name' => $routine->name,
                'slug' => $routine->slug,
                'owner_id' => $routine->owner_id,
            ]);
        });

        $secondUserRoutines->each(function ($routine) use ($response) {
            $response->assertJsonFragment([
                'id' => $routine->id,
                'name' => $routine->name,
                'slug' => $routine->slug,
                'owner_id' => $routine->owner_id,
            ]);
        });

    }

    #[Test]
    public function it_only_shows_a_user_their_routines(): void
    {
        // Given
        $firstUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->firstUser->id,
        ]);

        $secondUserRoutines = Routine::factory()->count(3)->create([
            'owner_id' => $this->secondUser->id,
        ]);

        // When
        $response = $this->actingAs($this->firstUser)->get(route('routines.index'));

        // Then
        $response->assertOk();

        $firstUserRoutines->each(function ($routine) use ($response) {
            $response->assertJsonFragment([
                'id' => $routine->id,
                'name' => $routine->name,
                'slug' => $routine->slug,
                'owner_id' => $routine->owner_id,
            ]);
        });

        $secondUserRoutines->each(function ($routine) use ($response) {
            $response->assertJsonMissing([
                'id' => $routine->id,
                'name' => $routine->name,
                'slug' => $routine->slug,
                'owner_id' => $routine->owner_id,
            ]);
        });

    }
}
