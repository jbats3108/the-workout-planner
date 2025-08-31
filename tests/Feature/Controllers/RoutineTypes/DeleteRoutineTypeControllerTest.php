<?php

namespace Tests\Feature\Controllers\RoutineTypes;

use App\Models\RoutineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DeleteRoutineTypeControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_only_allows_admins_to_delete_routine_types(): void
    {
        // Given
        $routineType = RoutineType::factory()->create();

        // When
        $response = $this->actingAs($this->user)->delete(route('routine-types.delete', ['routineType' => $routineType->id]));

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_deletes_the_routine_type(): void
    {
        // Given
        $routineType = RoutineType::factory()->create();

        // When
        $response = $this->actingAs($this->adminUser)->delete(route('routine-types.delete', ['routineType' => $routineType->id]));

        // Then
        $response->assertRedirect();
        $this->assertSoftDeleted(RoutineType::class, ['id' => $routineType->id]);

    }
}
