<?php

namespace Tests\Feature\Admin\Http\Controllers;

use App\Auth\Models\RegistrationInvite;
use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function admins_can_view_admin_pages(): void
    {
        MuscleGroup::factory()->create(['name' => 'Chest', 'slug' => 'chest']);
        Exercise::factory()->create(['name' => 'Bench', 'slug' => 'bench', 'user_id' => null]);

        $this->actingAs($this->adminUser)->get(route('admin.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/Index'));

        $this->actingAs($this->adminUser)->get(route('admin.exercises'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Exercises')
                ->has('muscle_groups')
                ->loadDeferredProps(fn ($page) => $page->has('exercises')));

        $this->actingAs($this->adminUser)->get(route('admin.muscle-groups'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/MuscleGroups')
                ->has('muscle_groups'));

        $this->actingAs($this->adminUser)->get(route('admin.users'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Users')
                ->has('users'));

        $this->actingAs($this->adminUser)->get(route('admin.invites'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Invites')
                ->has('invites'));
    }

    #[Test]
    public function admins_can_create_and_revoke_invites(): void
    {
        $this->actingAs($this->adminUser)
            ->post(route('admin.invites.store'), [
                'note' => 'Gym buddy',
                'role' => 'user',
                'expires_in_days' => 3,
            ])
            ->assertRedirect(route('admin.invites'))
            ->assertSessionHas('invite_url');

        $inviteId = RegistrationInvite::query()->firstOrFail()->id;

        $this->actingAs($this->adminUser)
            ->post(route('admin.invites.revoke', $inviteId))
            ->assertRedirect(route('admin.invites'));

        $this->assertNotNull(RegistrationInvite::query()->find($inviteId)?->revoked_at);
    }

    #[Test]
    public function non_admins_cannot_view_admin_pages(): void
    {
        $this->actingAs($this->user)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.exercises'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.muscle-groups'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.invites'))->assertForbidden();
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }
}
