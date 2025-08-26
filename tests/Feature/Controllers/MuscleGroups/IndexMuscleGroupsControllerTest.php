<?php

namespace Tests\Feature\Controllers\MuscleGroups;

use App\DataTransferObjects\MuscleGroups\MuscleGroupData;
use App\Models\MuscleGroup;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class IndexMuscleGroupsControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    #[Test]
    #[DataProvider('provideUserRoles')]
    public function it_returns_all_muscle_groups(string $userRole): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        $exercises = MuscleGroup::factory()->count(3)->create();

        $user = $this->createUser($userRole);

        // When
        $response = $this->actingAs($user)->get(route('muscle-groups.index'));

        // Then
        $response->assertOk();

        $exercises->each(fn (MuscleGroup $exercise) => $this->assertContains(
            MuscleGroupData::from($exercise)->toArray(),
            $response->json())
        );

    }
}
