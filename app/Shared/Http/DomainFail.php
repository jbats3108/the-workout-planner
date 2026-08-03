<?php

namespace App\Shared\Http;

use Illuminate\Http\RedirectResponse;
use Throwable;

final class DomainFail
{
    public static function back(Throwable $exception, string $bag): RedirectResponse
    {
        return back()->withErrors([$bag => $exception->getMessage()]);
    }
}
