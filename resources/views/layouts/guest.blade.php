<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="data:,">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head-scripts')
    @livewireStyles
</head>
<body class="antialiased">
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
