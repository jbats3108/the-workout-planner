<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo('view all routines');
    }
}
