<?php

namespace Tests\Feature\Controllers\MuscleGroups;

use App\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreMuscleGroupControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    #[Test]
    public function it_rejects_requests_from_non_admins(): void
    {
        // Given
        $request = [
            'name' => 'Muscle Group',
            'slug' => 'muscle-group',
        ];

        // When
        $response = $this->actingAs($this->user)->post(route('muscle-groups.store'), $request);

        // Then
        $response->assertForbidden();
    }

    #[Test]
    #[TestWith(['name'])]
    #[TestWith(['slug'])]
    public function it_rejects_requests_without_a_name_or_slug(string $missingKey): void
    {
        // Given
        $request = [
            'name' => 'Name',
            'slug' => 'slug',
        ];

        unset($request[$missingKey]);

        // When
        $response = $this->actingAs($this->adminUser)->post(route('muscle-groups.store'), $request);

        // Then
        $response->assertSessionHasErrors($missingKey);
    }

    #[Test]
    public function it_requires_the_name_to_be_unique(): void
    {
        // Given
        $existingMuscleGroup = MuscleGroup::factory()->create();

        $request = [
            'name' => $existingMuscleGroup->name,
            'slug' => 'new-slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('muscle-groups.store'), $request);

        // Then
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function it_requires_the_slug_to_be_unique(): void
    {
        // Given
        $existingMuscleGroup = MuscleGroup::factory()->create();

        $request = [
            'name' => 'Name',
            'slug' => $existingMuscleGroup->slug,
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('muscle-groups.store'), $request);

        // Then
        $response->assertSessionHasErrors('slug');
    }

    #[Test]
    public function it_creates_a_new_muscle_group(): void
    {
        // Given
        $request = [
            'name' => 'Name',
            'slug' => 'slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('muscle-groups.store'), $request);

        // Then
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas(MuscleGroup::class, [
            'name' => 'Name',
            'slug' => 'slug',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }
}
