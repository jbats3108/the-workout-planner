<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(2)->withRole('admin')->sequence(fn (Sequence $sequence) => [
            'name' => 'Admin '.$sequence->index + 1,
            'email' => 'admin'.$sequence->index + 1 .'@test.com',
            'password' => bcrypt('password'),
        ])->create();

        User::factory(3)->withRole('user')->sequence(
            fn (Sequence $sequence) => [
                'name' => 'User '.$sequence->index + 1,
                'email' => 'user'.$sequence->index + 1 .'@test.com',
                'password' => bcrypt('password'),
            ]
        )->create();
    }
}
