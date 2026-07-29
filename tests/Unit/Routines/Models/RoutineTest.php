<?php

namespace Tests\Unit\Routines\Models;

use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        // Given
        $user = User::factory()->create();

        // When
        $routine = Routine::factory()->withUser($user)->create();

        // Then
        $this->assertTrue($routine->user->is($user));
    }

    #[Test]
    public function it_has_blocks(): void
    {
        // Given
        $routine = Routine::factory()->create();

        RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);

        // When / Then
        $this->assertCount(1, $routine->blocks);
    }

    #[Test]
    public function renaming_does_not_change_the_slug(): void
    {
        $routine = Routine::factory()->create([
            'name' => 'Barbell Strength',
            'slug' => 'barbell-strength',
        ]);

        $routine->update(['name' => 'New Name']);

        $this->assertSame('barbell-strength', $routine->fresh()->slug);
    }
}
