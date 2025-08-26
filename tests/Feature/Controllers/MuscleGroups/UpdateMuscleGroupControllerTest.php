<?php

namespace Tests\Feature\Controllers\MuscleGroups;

use App\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class UpdateMuscleGroupControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_only_allows_admins_to_update_a_muscle_group(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();

        // When
        $response = $this->actingAs($this->user)->put(route('muscle-groups.update', ['muscleGroup' => $muscleGroup->id]));

        // Then
        $response->assertForbidden();

    }

    #[Test]
    #[TestWith(['name'])]
    #[TestWith(['slug'])]
    public function it_rejects_requests_without_a_name_or_slug(string $missingKey): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();

        $request = [
            'name' => 'Name',
            'slug' => 'slug',
        ];

        unset($request[$missingKey]);

        // When
        $response = $this->actingAs($this->adminUser)->put(
            route('muscle-groups.update',
                ['muscleGroup' => $muscleGroup->id],

            ), $request);

        // Then
        $response->assertSessionHasErrors($missingKey);
    }

    #[Test]
    public function it_updates_the_muscle_group(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create([
            'name' => 'Name',
            'slug' => 'slug',
        ]);

        $request = [
            'name' => 'New Name',
            'slug' => 'new-slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->put(
            route('muscle-groups.update',
                ['muscleGroup' => $muscleGroup->id],
            ),
            $request,
        );

        // Then
        $response->assertRedirect(route('dashboard'));

        $muscleGroup->refresh();

        $this->assertSame($request['name'], $muscleGroup->name);
        $this->assertSame($request['slug'], $muscleGroup->slug);

    }
}
