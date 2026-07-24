<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'dark') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0a0a0a" media="(prefers-color-scheme: dark)">
        <meta name="theme-color" content="#f7f7f7" media="(prefers-color-scheme: light)">

        {{-- SVG only here; .ico is still on disk for older clients that request /favicon.ico directly --}}
        <link rel="icon" href="/favicon-dark.svg" type="image/svg+xml" data-app-favicon>

        {{-- Inline script: apply theme class + favicon before paint --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "dark" }}';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = appearance === 'dark'
                    || (appearance === 'system' && prefersDark)
                    || (appearance !== 'light' && appearance !== 'system');

                document.documentElement.classList.toggle('dark', isDark);

                const href = (isDark ? '/favicon-dark.svg' : '/favicon-light.svg') + '?theme=' + (isDark ? 'dark' : 'light');
                document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"]').forEach(function (el) {
                    el.remove();
                });
                var link = document.createElement('link');
                link.rel = 'icon';
                link.type = 'image/svg+xml';
                link.setAttribute('data-app-favicon', '');
                link.href = href;
                document.head.appendChild(link);
            })();
        </script>

        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: hsl(0 0% 97%);
            }

            html.dark {
                background-color: hsl(0 0% 4%);
            }
        </style>

        <title inertia>{{ config('app.name', 'OVRLOAD') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|jetbrains-mono:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
