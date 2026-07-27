<?php

namespace App\Shared\Http;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class SoftFail
{
    public const NOT_FOUND_MESSAGE = 'That link looks wrong. Check the URL and try again.';

    public const FORBIDDEN_MESSAGE = 'You do not have access to that.';

    /**
     * @var array<string, string>
     */
    private const ROUTE_RESOURCE_LABELS = [
        'workout' => 'Workout',
        'routine' => 'Routine',
        'exercise' => 'Exercise',
        'muscleGroup' => 'Muscle group',
        'set' => 'Set',
        'block' => 'Block',
        'invite' => 'Invite',
    ];

    public static function maybeRedirectNotFound(Request $request, NotFoundHttpException $exception): ?RedirectResponse
    {
        return self::maybeRedirect($request, self::notFoundMessage($exception, $request));
    }

    public static function maybeRedirectForbidden(Request $request): ?RedirectResponse
    {
        return self::maybeRedirect($request, self::forbiddenMessage($request));
    }

    public static function notFoundMessage(NotFoundHttpException $exception, Request $request): string
    {
        $label = self::resourceLabelFromException($exception) ?? self::resourceLabelFromRoute($request);

        if ($label === null) {
            return self::NOT_FOUND_MESSAGE;
        }

        return "{$label} not found. Check the URL and try again.";
    }

    public static function forbiddenMessage(Request $request): string
    {
        $label = self::resourceLabelFromRoute($request);

        if ($label === null) {
            return self::FORBIDDEN_MESSAGE;
        }

        return 'You do not have access to that '.Str::lower($label).'.';
    }

    public static function maybeRedirect(Request $request, string $message): ?RedirectResponse
    {
        if ($request->user() === null) {
            return null;
        }

        if ($request->is('admin', 'admin/*')) {
            return null;
        }

        if (! $request->isMethodSafe()) {
            return null;
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return null;
        }

        return redirect()
            ->back(fallback: route('dashboard'))
            ->with('error', $message);
    }

    private static function resourceLabelFromException(Throwable $exception): ?string
    {
        $previous = $exception->getPrevious();

        if (! $previous instanceof ModelNotFoundException) {
            return null;
        }

        $model = $previous->getModel();

        if (! is_string($model) || $model === '') {
            return null;
        }

        return Str::headline(class_basename($model));
    }

    private static function resourceLabelFromRoute(Request $request): ?string
    {
        $route = $request->route();

        if ($route === null) {
            return null;
        }

        foreach (self::ROUTE_RESOURCE_LABELS as $parameter => $label) {
            if ($route->hasParameter($parameter)) {
                return $label;
            }
        }

        return null;
    }
}
