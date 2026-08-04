<?php

namespace App\Settings\Http\Controllers;

use App\Settings\Services\UserDataExporter;
use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportUserDataController extends Controller
{
    public function __invoke(Request $request, UserDataExporter $exporter): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->refresh();
        $payload = $exporter->export($user);
        $filename = sprintf('ovrload-export-%s-%s.json', $user->id, now()->format('Y-m-d'));

        return response()->streamDownload(
            static function () use ($payload): void {
                echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            },
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }
}
