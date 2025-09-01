<?php

namespace Tests\Unit\DataTransferObjects\Routines;

use App\DataTransferObjects\Routines\StoreRoutineData;
use App\Models\RoutineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreRoutineDataTest extends TestCase
{
    use RefreshDatabase;
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
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
        $data = StoreRoutineData::from($createRoutineData);

        // Then
        $this->assertTrue($data->routineType->is($routineType));

    }
}
