<?php

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\CreateRoutineData;
use App\Models\RoutineType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRoutineDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->user = User::Factory()->create();
        $this->user->assignRole('user');
    }

    #[Test]
    public function it_resolves_the_routine_type_slug_to_a_model(): void
    {
        // Given
        $routineType = RoutineType::factory()->create();

        $createRoutineData = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'routine_type' => $routineType->getSlug(),
        ];

        $this->be($this->user);

        // When
        $data = CreateRoutineData::from($createRoutineData);

        // Then
        $this->assertTrue($data->routineType->is($routineType));

    }
}
