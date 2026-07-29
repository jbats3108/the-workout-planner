<?php

namespace Tests\Unit\Routines\Services;

use App\Routines\Models\Routine;
use App\Routines\Services\RoutineSlugGenerator;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineSlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_slugs_the_name(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            'barbell-strength',
            RoutineSlugGenerator::forUser($user, 'Barbell Strength'),
        );
    }

    public function test_it_suffixes_on_collision_within_the_same_user(): void
    {
        $user = User::factory()->create();
        Routine::factory()->withUser($user)->create([
            'name' => 'Barbell Strength',
            'slug' => 'barbell-strength',
        ]);

        $this->assertSame(
            'barbell-strength-2',
            RoutineSlugGenerator::forUser($user, 'Barbell Strength'),
        );
    }

    public function test_it_allows_the_same_slug_for_a_different_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        Routine::factory()->withUser($owner)->create([
            'name' => 'Barbell Strength',
            'slug' => 'barbell-strength',
        ]);

        $this->assertSame(
            'barbell-strength',
            RoutineSlugGenerator::forUser($other, 'Barbell Strength'),
        );
    }

    public function test_it_falls_back_when_the_name_slugs_to_empty(): void
    {
        $user = User::factory()->create();

        $this->assertSame('routine', RoutineSlugGenerator::forUser($user, '!!!'));
    }

    public function test_it_transliterates_unicode_names(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            'cafe-strength',
            RoutineSlugGenerator::forUser($user, 'Café Strength'),
        );
    }

    public function test_it_ignores_soft_deleted_slug_collisions(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->withUser($user)->create([
            'name' => 'Barbell Strength',
            'slug' => 'barbell-strength',
        ]);
        $routine->delete();

        // Unique index still covers soft-deleted rows, so suffix is required.
        $this->assertSame(
            'barbell-strength-2',
            RoutineSlugGenerator::forUser($user, 'Barbell Strength'),
        );
    }
}
