<?php

namespace Tests\Unit;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    #[Test]
    public function it_can_be_identified_as_an_admin(): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        // When
        $user = User::factory()->withRole('admin')->create();

        // Then
        $this->assertTrue($user->isAdmin());

    }

    #[Test]
    public function it_can_be_identified_as_not_an_admin(): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        // When
        $user = User::factory()->withRole('user')->create();

        // Then
        $this->assertFalse($user->isAdmin());

    }

    #[Test]
    public function it_is_linked_to_routines(): void
    {
        // Given
        $this->seed(RoleSeeder::class);
        $user = $this->createUser('user');

        $routines = Routine::factory(3)->withOwner($user)->create();

        // When
        $userRoutines = $user->routines;

        // Then
        $this->assertEquals($routines->fresh(),$userRoutines);

    }
}
