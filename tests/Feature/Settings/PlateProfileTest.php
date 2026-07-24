<?php

namespace Tests\Feature\Settings;

use App\Users\Models\PlateProfile;
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
        $this->assertDatabaseCount('plate_profiles', 0);

        $response = $this->actingAs($this->user)->get(route('training.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Training')
            ->has('plate_profile.bars', 1)
            ->has('plate_profile.plates', 7));

        $this->assertDatabaseHas('plate_profiles', [
            'user_id' => $this->user->id,
            'name' => 'Home gym',
        ]);
    }

    #[Test]
    public function it_updates_the_plate_profile(): void
    {
        $this->actingAs($this->user)->get(route('training.edit'));

        $response = $this->actingAs($this->user)->put(route('training.plates.update'), [
            'name' => 'Garage',
            'bars' => [
                ['name' => 'Short', 'weight_g' => 15000, 'is_default' => true],
            ],
            'plates' => [
                ['denomination_g' => 10000, 'count' => 4, 'colour' => 'green'],
                ['denomination_g' => 5000, 'count' => 2, 'colour' => null],
            ],
        ]);

        $response->assertRedirect(route('training.edit'));
        $response->assertSessionHas('success');

        $profile = PlateProfile::query()->where('user_id', $this->user->id)->first();
        $this->assertNotNull($profile);
        $this->assertSame('Garage', $profile->name);
        $this->assertCount(1, $profile->bars);
        $this->assertSame(15000, $profile->bars->first()->weight_g);
        $this->assertCount(2, $profile->plates);
    }
}
