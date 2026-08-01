<?php

namespace Tests\Feature\MuscleGroups\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DeleteMuscleGroupControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_only_allows_admins_to_delete_muscle_groups(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();

        // When
        $response = $this->actingAs($this->user)->delete(route('muscle-groups.delete', ['muscleGroup' => $muscleGroup->id]));

        // Then
        $response->assertForbidden();
    }

    #[Test]
    public function it_deletes_the_muscle_group(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();

        // When
        $response = $this->actingAs($this->adminUser)->delete(route('muscle-groups.delete', ['muscleGroup' => $muscleGroup->id]));

        // Then
        $response->assertRedirect(route('admin.muscle-groups'));
        $this->assertSoftDeleted(MuscleGroup::class, ['id' => $muscleGroup->id]);
    }

    #[Test]
    public function it_rejects_delete_when_exercises_still_reference_the_muscle_group(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();
        Exercise::factory()->create([
            'primary_muscle_group_id' => $muscleGroup->id,
        ]);

        // When
        $response = $this->actingAs($this->adminUser)->delete(route('muscle-groups.delete', ['muscleGroup' => $muscleGroup->id]));

        // Then
        $response->assertRedirect(route('admin.muscle-groups'));
        $response->assertSessionHas('error', 'Cannot delete a muscle group that is still used by exercises.');
        $this->assertDatabaseHas(MuscleGroup::class, [
            'id' => $muscleGroup->id,
            'deleted_at' => null,
        ]);
    }
}
