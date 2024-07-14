<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head class="h-full bg-gray-100">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Event Schedule</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/js/jquery-3.3.1.min.js',
    ])

    {{ $head }}
</head>

<body class="font-sans antialiased h-full">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        <div class="min-h-full">
            <div class="bg-gray-800 pb-32">
                @include('layouts.navigation')

                <header class="py-10">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h1 class="text-3xl font-bold tracking-tight text-white">{{ $header }}</h1>
                    </div>
                </header>
            </div>

            <main class="-mt-32">
                <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
                    <div class="rounded-lg bg-white px-5 py-6 shadow sm:px-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>