<?php

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local Sail/phone: generate absolute URLs for the host the browser actually used
 * (localhost on the PC, LAN IP on a phone) without rewriting APP_URL.
 */
class UseRequestRootUrl
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isLocal()) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }

        return $next($request);
    }
}
