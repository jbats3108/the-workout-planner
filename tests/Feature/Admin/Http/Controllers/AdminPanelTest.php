<?php

namespace Tests\Feature\Admin\Http\Controllers;

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
                ->has('exercises')
                ->has('muscle_groups'));

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
    }

    #[Test]
    public function non_admins_cannot_view_admin_pages(): void
    {
        $this->actingAs($this->user)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.exercises'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.muscle-groups'))->assertForbidden();
        $this->actingAs($this->user)->get(route('admin.users'))->assertForbidden();
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }
}
