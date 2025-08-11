<?php

namespace Tests\Unit;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

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
}
