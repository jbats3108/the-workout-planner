<?php

namespace Tests\Unit\DataTransferObjects\Routines;

use App\DataTransferObjects\Routines\StoreRoutineData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreRoutineDataTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_resolves_the_authenticated_user(): void
    {
        // Given
        $createRoutineData = [
            'name' => 'Test Routine',
        ];

        $this->be($this->user);

        // When
        $storeRoutineData = StoreRoutineData::from($createRoutineData);

        // Then
        $this->assertTrue($storeRoutineData->user->is($this->user));
    }

    #[Test]
    public function it_accepts_optional_deload_factors(): void
    {
        // Given
        $createRoutineData = [
            'name' => 'Test Routine',
            'deload_weight_factor' => 0.75,
            'deload_reps_factor' => 1.5,
        ];

        $this->be($this->user);

        // When
        $storeRoutineData = StoreRoutineData::from($createRoutineData);

        // Then
        $this->assertSame(0.75, $storeRoutineData->deloadWeightFactor);
        $this->assertSame(1.5, $storeRoutineData->deloadRepsFactor);
    }
}
