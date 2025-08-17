<?php

namespace Tests\Feature\Controllers\RoutineTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class CreateRoutineTypeControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    #[Test]
    public function it_is_only_accessible_to_admins(): void
    {
        // Given / When / Then
        $this->actingAs($this->user)->get(route('routine-types.create'))->assertForbidden();

    }

    #[Test]
    public function it_returns_the_create_routine_type_page(): void
    {
        // Given / When
        $response = $this->actingAs($this->adminUser)->get(route('routine-types.create'));

        // Then
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('RoutineTypes/Create'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }
}
