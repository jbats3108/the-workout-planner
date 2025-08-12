<?php

namespace Tests\Feature\Controllers\Routines;

use App\DataTransferObjects\Routines\RoutineData;
use App\Models\Routine;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowRoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->withRole('user')->create();
        $this->adminUser = User::factory()->withRole('admin')->create();
    }

    #[Test]
    public function it_allows_a_user_to_view_their_own_routine(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $response = $this->actingAs($this->user)->get(route('routines.show', $routine));

        // Then
        $response->assertOk();

        $this->assertSame(RoutineData::fromRoutine($routine)->toArray(), $response->json());
    }

    #[Test]
    public function it_allows_an_admin_to_view_any_routine(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $response = $this->actingAs($this->adminUser)->get(route('routines.show', $routine));

        // Then
        $response->assertOk();

        $this->assertSame(RoutineData::fromRoutine($routine)->toArray(), $response->json());
    }

    #[Test]
    public function it_does_not_allow_users_to_view_another_users_routine(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $otherUser = User::factory()->withRole('user')->create();

        // When
        $response = $this->actingAs($otherUser)->get(route('routines.show', $routine));

        // Then
        $response->assertForbidden();

    }
}
