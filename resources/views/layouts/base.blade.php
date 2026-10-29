<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') · TOKO SITI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|jetbrains-mono:500" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @yield('body')

    @if (session('status'))
        <div class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4"
             x-data="{ open: true }" x-init="setTimeout(() => open = false, 3500)">
            <div x-show="open"
                 x-transition:enter="transition duration-300 ease-glass" x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
                 role="status"
                 class="glass-strong pointer-events-auto rounded-2xl px-5 py-3 text-sm font-semibold text-ink-900 shadow-xl">
                {{ session('status') }}
            </div>
        </div>
    @endif
</body>
</html>
