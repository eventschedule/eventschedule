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

    <form method="POST" action="{{ route('app.update') }}" enctype="multipart/form-data" class="mt-6">
        @csrf

        <div class="mt-4">
            <x-input-label for="package" :value="__('Upload release ZIP (optional)')" />
            <input id="package" name="package" type="file" accept=".zip"
                class="mt-1 block w-full text-gray-900 dark:text-gray-100" />
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Use this when the server cannot reach GitHub; the archive will be applied directly.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('package')" />
        </div>

        <x-primary-button class="mt-6">{{ __('messages.update') }}</x-primary-button>

        @if ($version_installed != $version_available)
            @php
                $downloadUrl = $version_available ? \App\Support\UpdateConfigManager::makeReleaseDownloadUrl($version_available) : null;
                $downloadLabel = config('self-update.repository_types.github.package_file_name', 'eventschedule.zip');

                if ($downloadUrl) {
                    $path = parse_url($downloadUrl, PHP_URL_PATH);
                    if (is_string($path)) {
                        $basename = basename($path);

                        if (! empty($basename)) {
                            $downloadLabel = $basename;
                        }
                    }
                }
            @endphp
            @if ($downloadUrl)
                <div class="text-gray-600 dark:text-gray-400 pt-6">
                    {!! __('messages.app_update_tip', ['link' => '<a href="' . e($downloadUrl) . '" class="hover:underline">' . e($downloadLabel) . '</a>']) !!}
                </div>
            @endif
        @else
            <div class="text-gray-600 dark:text-gray-400 pb-4">
                <b>{{ __('messages.up_to_date') }}</b>
            </div>

        @endif

    </form>

</section>
