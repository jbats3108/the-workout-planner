<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\RoutineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreRoutineTypeControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
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
        $response = $this->actingAs($this->adminUser)->post(route('routine-types.store'), $request);

        // Then
        $response->assertSessionHasErrors($missingKey);
    }

    #[Test]
    public function it_requires_the_name_to_be_unique(): void
    {
        // Given
        $existingRoutineType = RoutineType::factory()->create();

        $request = [
            'name' => $existingRoutineType->name,
            'slug' => 'new-slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('routine-types.store'), $request);

        // Then
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function it_requires_the_slug_to_be_unique(): void
    {
        // Given
        $existingRoutineType = RoutineType::factory()->create();

        $request = [
            'name' => 'Name',
            'slug' => $existingRoutineType->slug,
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('routine-types.store'), $request);

        // Then
        $response->assertSessionHasErrors('slug');
    }

    #[Test]
    public function it_only_allows_admins_to_create_routines(): void
    {
        // Given
        $request = [
            'name' => 'Name',
            'slug' => 'slug',
        ];

        // When
        $response = $this->actingAs($this->user)->post(route('routine-types.store'), $request);

        // Then
        $response->assertForbidden();
    }

    #[Test]
    public function it_creates_a_new_routine_type(): void
    {
        // Given
        $request = [
            'name' => 'Name',
            'slug' => 'slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('routine-types.store'), $request);

        // Then
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas(RoutineType::class, [
            'name' => 'Name',
            'slug' => 'slug',
        ]);
    }
}
