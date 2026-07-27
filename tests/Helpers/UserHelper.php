<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;

trait UserHelper
{
    private User $adminUser;

    private User $user;

    private User $secondUser;

    public function seedUsers(bool $withCatalogAndRoutines = true): void
    {
        if ($withCatalogAndRoutines) {
            $this->seed();
        } else {
            $this->seed([
                RoleSeeder::class,
                UserSeeder::class,
            ]);
        }

        $this->adminUser = User::whereHas('roles', function ($query): void {
            $query->where('name', 'admin');
        })->first();

        $users = User::whereHas('roles', function ($query): void {
            $query->where('name', 'user');
        })->get();

        $this->user = $users->first();
        $this->secondUser = $users->last();

    }

    public function createUser(string $role = 'admin'): User
    {
        return User::factory()->withRole($role)->create();
    }

    /**
     * @return array<string, string[]>
     */
    public static function provideUserRoles(): array
    {
        return
            [
                'Admin' => ['admin'],
                'User' => ['user'],
            ];
    }
}
