<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\User;

trait UserHelper
{
    private User $adminUser;

    private User $user;

    private User $secondUser;

    public function seedUsers(): void
    {
        $this->seed();

        $this->adminUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->get();

        $this->user = $users->first();
        $this->secondUser = $users->last();

    }

    public function createUser(string $role = 'admin'): User
    {
        return User::factory()->withRole($role)->create();
    }
}
