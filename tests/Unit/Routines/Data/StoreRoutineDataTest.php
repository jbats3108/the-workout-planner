<?php

namespace Tests\Unit\Routines\Data;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\Routines\Data\StoreRoutineData;
use App\Users\Enums\ProgressionStyle;
use App\Users\Enums\ProgressiveMidBlock;
use Database\Seeders\ExerciseProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreRoutineDataTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers(false);
        $this->seed(ExerciseProfileSeeder::class);
    }

    #[Test]
    public function it_resolves_the_authenticated_user(): void
    {
        // Given
        $createRoutineData = [
            'name' => 'Test Routine',
            'default_exercise_profile_id' => $this->profileId(),
        ];

        $this->be($this->user);

        // When
        $storeRoutineData = StoreRoutineData::from($createRoutineData);

        // Then
        $this->assertTrue($storeRoutineData->user->is($this->user));
    }

    #[Test]
    public function it_accepts_optional_deload_every_n(): void
    {
        // Given
        $createRoutineData = [
            'name' => 'Test Routine',
            'default_exercise_profile_id' => $this->profileId(),
            'deload_every_n' => 4,
        ];

        $this->be($this->user);

        // When
        $storeRoutineData = StoreRoutineData::from($createRoutineData);

        // Then
        $this->assertSame(4, $storeRoutineData->deloadEveryN);
    }

    #[Test]
    public function it_accepts_optional_progression_style(): void
    {
        $this->be($this->user);

        $storeRoutineData = StoreRoutineData::from([
            'name' => 'Test Routine',
            'default_exercise_profile_id' => $this->profileId(),
            'progression_style' => 'progressive_overload',
            'progressive_mid_block' => 'auto',
        ]);

        $this->assertSame(ProgressionStyle::ProgressiveOverload, $storeRoutineData->progressionStyle);
        $this->assertSame(ProgressiveMidBlock::Auto, $storeRoutineData->progressiveMidBlock);
    }

    private function profileId(): int
    {
        return ExerciseProfile::query()->where('slug', 'preset-strength')->valueOrFail('id');
    }
}
