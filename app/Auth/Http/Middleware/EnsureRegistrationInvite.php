<?php

namespace App\Auth\Http\Middleware;

use App\Auth\Services\RegistrationInviteService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationInvite
{
    public function __construct(
        private readonly RegistrationInviteService $invites,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->query('invite') ?? $request->input('invite');

        if (! is_string($provided) || $provided === '' || ! $this->invites->accepts($provided)) {
            abort(404);
        }

        return $next($request);
    }
}
