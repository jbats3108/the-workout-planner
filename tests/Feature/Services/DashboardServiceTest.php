<?php

namespace Tests\Feature\Services;

use App\DataTransferObjects\Routines\RoutineData;
use App\Models\Routine;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();

        // Drop seeded routines
        Routine::truncate();
    }

    #[Test]
    public function it_returns_the_users_routines(): void
    {
        // Given
        $routines = Routine::factory(5)->withOwner($this->user)->create();

        $dashboardService = new DashboardService;

        // When
        $dashboardData = $dashboardService->getDashboardData($this->user);

        // Then
        $dashboardRoutines = $dashboardData->routines;

        $this->assertCount(5, $dashboardRoutines);

        $routineData = RoutineData::collect($routines);

        $this->assertEquals($routineData, $dashboardRoutines);

    }
}
