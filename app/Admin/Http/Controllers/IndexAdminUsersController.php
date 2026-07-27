<?php

namespace App\Admin\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexAdminUsersController extends Controller
{
    public function __invoke(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('email')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values()->all(),
                'created_at' => $user->created_at?->toDateString(),
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
        ]);
    }
}
