<?php

namespace Tests\Feature\Routines\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Data\Editor\SyncRoutineData;
use App\Routines\Models\Routine;
use App\Routines\Services\RoutineEditorService;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\RoutineEditorPayload;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DuplicateRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers(false);
    }

    #[Test]
    public function it_duplicates_own_routine_and_redirects_to_editor(): void
    {
        $routine = $this->seededRoutineFor($this->user);

        $response = $this->makeRequest($routine);

        $copy = Routine::query()
            ->where('user_id', $this->user->id)
            ->where('name', $routine->name.' (copy)')
            ->first();

        $this->assertNotNull($copy);
        $response->assertRedirect(route('routines.edit', $copy));
        $response->assertSessionHas('success', 'Routine duplicated.');
        $this->assertSame(2, $copy->blocks()->count());
    }

    #[Test]
    public function it_allows_admins_to_duplicate_any_routine_into_their_account(): void
    {
        $routine = $this->seededRoutineFor($this->user);

        $response = $this->actingAs($this->adminUser)
            ->post(route('routines.duplicate', $routine));

        $copy = Routine::query()
            ->where('user_id', $this->adminUser->id)
            ->where('name', $routine->name.' (copy)')
            ->first();

        $this->assertNotNull($copy);
        $response->assertRedirect(route('routines.edit', $copy));
        $this->assertSame($this->adminUser->id, $copy->user_id);
    }

    #[Test]
    public function it_prevents_users_duplicating_other_users_routines(): void
    {
        $otherUser = User::factory()->withRole('user')->create();
        $routine = Routine::factory()->create(['user_id' => $otherUser->id]);
        $before = Routine::query()->where('user_id', $this->user->id)->count();

        $response = $this->makeRequest($routine);

        $response->assertNotFound();
        $this->assertSame($before, Routine::query()->where('user_id', $this->user->id)->count());
    }

    #[Test]
    public function guests_cannot_duplicate_routines(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $this->post(route('routines.duplicate', $routine))
            ->assertRedirect(route('login'));
    }

    /**
     * @return TestResponse<RedirectResponse>
     */
    private function makeRequest(Routine $routine): TestResponse
    {
        return $this->actingAs($this->user)->post(route('routines.duplicate', $routine));
    }

    private function seededRoutineFor(User $user): Routine
    {
        $routine = Routine::factory()->withUser($user)->create(['name' => 'Clone Me']);
        $exercise = Exercise::factory()->create();

        app(RoutineEditorService::class)->sync($routine, SyncRoutineData::from([
            'name' => 'Clone Me',
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'blocks' => [
                RoutineEditorPayload::block($exercise->id, [
                    'working_weight_kg' => 50,
                    'prescribed_reps' => 8,
                    'has_setup_after' => true,
                    'working' => ['set_count' => 3, 'rest_seconds' => 90],
                    'warm_up' => [
                        'set_count' => 1,
                        'rest_seconds' => 45,
                        'steps' => [['percent' => 50, 'reps' => 5]],
                    ],
                ]),
                RoutineEditorPayload::block($exercise->id, [
                    'working_weight_kg' => 40,
                    'prescribed_reps' => 10,
                    'working' => ['set_count' => 2, 'rest_seconds' => 60],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 30, 'steps' => []],
                ]),
            ],
        ]));

        return $routine->fresh();
    }
}
