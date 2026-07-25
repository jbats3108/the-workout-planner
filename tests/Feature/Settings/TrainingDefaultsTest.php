<?php

namespace Tests\Feature\Settings;

use App\Users\Enums\WarmUpDefaultsScope;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class TrainingDefaultsTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_renders_resolved_warm_up_defaults(): void
    {
        $response = $this->actingAs($this->user)->get(route('training.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Training')
            ->where('using_app_fallback', true)
            ->where('warm_up_defaults_scope', 'all_blocks')
            ->has('warm_up_steps_default', 3)
            ->has('plate_profile'));
    }

    #[Test]
    public function it_saves_warm_up_step_defaults(): void
    {
        $response = $this->actingAs($this->user)->put(route('training.update'), [
            'warm_up_steps_default' => [
                ['percent' => 45, 'reps' => 6],
                ['percent' => 70, 'reps' => 2],
            ],
            'warm_up_defaults_scope' => 'first_block',
        ]);

        $response->assertRedirect(route('training.edit'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertSame(
            [
                ['percent' => 45, 'reps' => 6],
                ['percent' => 70, 'reps' => 2],
            ],
            $this->user->warm_up_steps_default
        );
        $this->assertSame(
            [
                ['percent' => 45, 'reps' => 6],
                ['percent' => 70, 'reps' => 2],
            ],
            $this->user->resolvedWarmUpStepsDefault()
        );
        $this->assertSame(WarmUpDefaultsScope::FirstBlock, $this->user->warm_up_defaults_scope);
    }

    #[Test]
    public function it_resets_to_app_fallback(): void
    {
        $this->user->update([
            'warm_up_steps_default' => [['percent' => 50, 'reps' => 8]],
        ]);

        $response = $this->actingAs($this->user)->post(route('training.reset'));

        $response->assertRedirect(route('training.edit'));
        $this->assertNull($this->user->fresh()->warm_up_steps_default);
        $this->assertSame(User::fallbackWarmUpSteps(), $this->user->fresh()->resolvedWarmUpStepsDefault());
    }
}
