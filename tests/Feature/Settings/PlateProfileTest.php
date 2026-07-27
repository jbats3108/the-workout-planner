<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class PlateProfileTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function training_page_seeds_a_default_plate_profile(): void
    {
        $this->actingAs($this->user)
            ->get(route('training.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/Training')
                ->has('plate_profile.bars', 1)
                ->has('plate_profile.plates', 7));
    }

    #[Test]
    public function it_updates_the_plate_profile(): void
    {
        $this->actingAs($this->user)->get(route('training.edit'));

        $this->actingAs($this->user)
            ->put(route('training.plates.update'), [
                'name' => 'Garage',
                'bars' => [
                    ['name' => 'Short', 'weight_g' => 15000, 'is_default' => true],
                ],
                'plates' => [
                    ['denomination_g' => 10000, 'count' => 4, 'colour' => 'green'],
                    ['denomination_g' => 5000, 'count' => 2, 'colour' => null],
                ],
            ])
            ->assertRedirect(route('training.edit'))
            ->assertSessionHas('success');
    }
}
