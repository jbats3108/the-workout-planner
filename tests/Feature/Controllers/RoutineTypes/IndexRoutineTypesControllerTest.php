<?php

namespace Tests\Feature\Controllers\RoutineTypes;

use App\DataTransferObjects\RoutineTypes\RoutineTypeData;
use App\Models\RoutineType;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class IndexRoutineTypesControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    #[Test]
    #[DataProvider('provideUserRoles')]
    public function it_returns_all_routine_types(string $userRole): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        $routineTypes = RoutineType::factory(5)->create();

        $user = $this->createUser($userRole);

        // When
        $response = $this->actingAs($user)->get(route('routine-types.index'));

        // Then
        $response->assertOk();

        $routineTypes->each(fn (RoutineType $routineType) => $this->assertContains(RoutineTypeData::from($routineType)->toArray(), $response->json()));
    }
}
