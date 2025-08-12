<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admins_cannot_update_user_routines(): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        $user = User::factory()->withRole('user')->create();

        $admin = User::factory()->withRole('admin')->create();

        $routine = Routine::factory()->withOwner($user)->create();

        $updatePayload = [
            'name' => 'New Name',
            'slug' => 'new-slug',
        ];

        // When
        $response = $this->actingAs($admin)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function users_can_only_update_their_own_routines(): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        $user = User::factory()->withRole('user')->create();
        $otherUser = User::factory()->withRole('user')->create();

        $routine = Routine::factory()->withOwner($user)->create();

        $updatePayload = [
            'name' => 'New Name',
        ];

        // When
        $response = $this->actingAs($otherUser)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_updates_the_routine_details(): void
    {
        // Given
        $this->seed(RoleSeeder::class);
        $user = User::factory()->withRole('user')->create();

        $routine = Routine::factory()->withOwner($user)->create();

        $updatePayload = [
            'name' => 'New Name',
            'slug' => 'new-name',
        ];

        // When
        $response = $this->actingAs($user)->put(route('routines.update', $routine), $updatePayload);

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
        $this->seed(RoleSeeder::class);
        $user = User::factory()->withRole('user')->create();
        $routine = Routine::factory()->withOwner($user)->create([
            'name' => 'Name',
            'slug' => 'name',
        ]);

        $updatePayload = [
            'name' => 'New Name',
        ];

        // When
        $response = $this->actingAs($user)->put(route('routines.update', $routine), $updatePayload);

        // Then
        $response->assertRedirect(route('routines.show', $routine));

        $routine->refresh();

        $this->assertSame('New Name', $routine->name);
        $this->assertSame('name', $routine->slug);

    }
}
