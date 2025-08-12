<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class UpdateRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function admins_cannot_update_user_routines(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $updatePayload = [
            'name' => 'New Name',
            'slug' => 'new-slug',
        ];

        // When
        $response = $this->actingAs($this->adminUser)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function users_can_only_update_their_own_routines(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $updatePayload = [
            'name' => 'New Name',
        ];

        // When
        $response = $this->actingAs($this->secondUser)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_updates_the_routine_details(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $updatePayload = [
            'name' => 'New Name',
            'slug' => 'new-name',
        ];

        // When
        $response = $this->actingAs($this->user)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertRedirect(route('routines.show', $routine));

        $routine->refresh();

        $this->assertSame('New Name', $routine->name);
        $this->assertSame('new-name', $routine->slug);

    }

    #[Test]
    public function it_does_not_override_the_slug_field_if_this_is_not_provided(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create([
            'name' => 'Name',
            'slug' => 'name',
        ]);

        $updatePayload = [
            'name' => 'New Name',
        ];

        // When
        $response = $this->actingAs($this->user)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertRedirect(route('routines.show', $routine));

        $routine->refresh();

        $this->assertSame('New Name', $routine->name);
        $this->assertSame('name', $routine->slug);

    }
}
