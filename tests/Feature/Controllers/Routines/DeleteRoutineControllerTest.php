<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DeleteRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_allows_admins_to_delete_all_routines(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $route = route('routines.delete', ['routine' => $routine->id]);
        $response = $this->actingAs($this->adminUser)->delete($route);

        // Then
        $response->assertRedirect();
        $this->assertSoftDeleted(Routine::class, ['id' => $routine->id]);

    }

    #[Test]
    public function it_allows_users_to_delete_their_own_routines(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $response = $this->makeRequest($routine);

        // Then
        $response->assertRedirect();
        $this->assertSoftDeleted(Routine::class, ['id' => $routine->id]);

    }

    #[Test]
    public function it_prevents_users_deleting_other_users_routines(): void
    {
        // Given
        $otherUser = User::factory()->withRole('user')->create();

        $routine = Routine::factory()->create([
            'owner_id' => $otherUser->id,
        ]);

        // When
        $response = $this->makeRequest($routine);

        // Then
        $response->assertForbidden();

    }

    /**
     * @return TestResponse<RedirectResponse>
     */
    private function makeRequest(Routine $routine): TestResponse
    {
        $route = route('routines.delete', ['routine' => $routine->id]);

        return $this->actingAs($this->user)->delete($route);
    }
}
