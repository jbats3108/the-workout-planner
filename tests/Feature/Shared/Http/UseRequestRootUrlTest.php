<?php

namespace Tests\Feature\Shared\Http;

use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UseRequestRootUrlTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function local_redirects_use_the_request_host_so_phone_lan_urls_stay_on_lan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://192.168.0.50:8000/login')
            ->assertRedirect('http://192.168.0.50:8000/dashboard');
    }

    #[Test]
    public function local_redirects_still_work_via_localhost(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://localhost:8000/login')
            ->assertRedirect('http://localhost:8000/dashboard');
    }
}
