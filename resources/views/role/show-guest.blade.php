<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ str_replace(':name', $role->name, __(':name Event Schedule'))  }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased dark:bg-black dark:text-white/50">

    <div class="p-10 max-w-4xl mx-auto px-4">
        <div class="flex items-center justify-between pb-5">
            <div>
                @if ($role->profile_image_url)
                <img src="{{ $role->profile_image_url }}" style="max-height:100px" />
                @else
                <div class="text-5xl font-bold">
                    {{ $role->name }}
                </div>
                @endif
            </div>
            <div>
                <a href="">
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ __('Follow') }}
                    </button>
                </a>
            </div>
        </div>

        @include('role/partials/calendar', ['showAdd' => false])

    </div>

</body>