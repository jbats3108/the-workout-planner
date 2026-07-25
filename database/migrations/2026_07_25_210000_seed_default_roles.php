<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('user', 'web');
    }

    public function down(): void
    {
        Role::query()->whereIn('name', ['admin', 'user'])->where('guard_name', 'web')->delete();
    }
};
