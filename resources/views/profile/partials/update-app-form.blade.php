<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.app_update') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.app_update_help') }}
        </p>
    </header>

    <div class="space-y-4 pt-6">
        <div class="flex items-center">
            <span class="text-gray-600 dark:text-gray-400 w-[160px] pr-4">{{ __('messages.installed_version') }}:</span>
            <span class="font-medium text-gray-700 dark:text-gray-300">
                {{ $version_installed }}
            </span>
        </div>

        <div class="flex items-center">
            <span class="text-gray-600 dark:text-gray-400 w-[160px] pr-4">{{ __('messages.latest_version') }}:</span>
            <span class="font-medium text-gray-700 dark:text-gray-300">
                {{ $version_available }}
            </span>
        </div>

    </div>

    @if ($version_installed != $version_available)
        <div class="text-gray-600 dark:text-gray-400 pt-4">
            {!! __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . $version_available . '/eventschedule.zip" class="hover:underline">eventschedule.zip</a>']) !!}
        </div>
    @endif

    <form method="post" action="{{ route('app.update') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('patch')

    <div class="gap-4 pt-8">
        @if ($version_installed != $version_available)
            <x-primary-button>{{ __('messages.update') }}</x-primary-button>
        @else
            <div class="text-gray-600 dark:text-gray-400 pb-4"> 
                {{ __('messages.up_to_date') }}
            </div>

        @endif
    </div>

    </form>

</section>
