<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'QICResolve-AI' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 px-5 py-6 text-white">
            <h1 class="text-xl font-bold">QICResolve-AI</h1>
            <p class="mt-1 text-xs text-slate-400">
                Quality Issue Management
            </p>

            <nav class="mt-8 space-y-2">
                <a href="{{ route('dashboard') }}"
                   class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Dashboard
                </a>

                <a href="{{ route('issues.index') }}"
                   class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Quality Issues
                </a>
            </nav>
        </aside>

        <main class="flex-1">
            <header class="border-b bg-white px-8 py-4">
                <h2 class="font-semibold">
                    {{ $header ?? 'Dashboard' }}
                </h2>
            </header>

            <section class="p-8">
                {{ $slot }}
            </section>
        </main>
    </div>

    @livewireScripts
</body>
</html>