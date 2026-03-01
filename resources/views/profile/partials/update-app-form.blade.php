<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('messages.app_update') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.app_update_help') }}
        </p>
    </header>

    <div class="space-y-4 pt-6">
        <div class="flex items-center">
            <span class="text-gray-600 dark:text-gray-400 w-[160px] pe-4">{{ __('messages.installed_version') }}:</span>
            <span class="font-medium text-gray-700 dark:text-gray-300">
                {{ $version_installed }}
            </span>
        </div>

        <div class="flex items-center">
            <span class="text-gray-600 dark:text-gray-400 w-[160px] pe-4">{{ __('messages.latest_version') }}:</span>
            <span class="font-medium text-gray-700 dark:text-gray-300">
                {{ $version_available }}
            </span>
        </div>

    </div>

    <form method="POST" action="{{ route('app.update') }}" enctype="multipart/form-data" class="mt-6">
        @csrf

    @if ($version_installed != $version_available)
        <x-primary-button>{{ __('messages.update') }}</x-primary-button>

        <div class="text-gray-600 dark:text-gray-400 pt-6"> 
            {!! __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . $version_available . '/eventschedule.zip" class="hover:underline">eventschedule.zip</a>']) !!}
        </div>
    @else
        <div class="text-gray-600 dark:text-gray-400 pb-4"> 
            <b>{{ __('messages.up_to_date') }}</b>
        </div>

    @endif

    </form>

</section>
