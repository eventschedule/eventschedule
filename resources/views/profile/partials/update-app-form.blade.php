<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.app_update_help') }}</p>

    <div class="form-kit-fields">
        {{-- Two short lines, kept narrow so each value sits beside its name. --}}
        <div class="max-w-xs">
            <div class="event-setting">
                <span class="event-setting-label">{{ __('messages.installed_version') }}</span>
                <span class="settings-mono text-gray-700 dark:text-gray-300">{{ $version_installed }}</span>
            </div>
            <div class="event-setting">
                <span class="event-setting-label">{{ __('messages.latest_version') }}</span>
                <span class="settings-mono text-gray-700 dark:text-gray-300">{{ $version_available ?? __('messages.unknown') }}</span>
            </div>
        </div>

        {{-- $update_available, not a string comparison of the two versions. A failed lookup leaves
             $version_available null, and comparing that against the installed version used to render
             an Update button during any GitHub outage. --}}
        @if ($update_available)
            {{-- It downloads a release, replaces the app's files and migrates the database, so it
                 asks first. It used to start on the click. --}}
            <form method="POST" action="{{ route('app.update') }}" enctype="multipart/form-data" class="mt-5" data-confirm="{{ __('messages.are_you_sure') }}">
                @csrf
                <x-brand-button type="submit">{{ __('messages.update') }}</x-brand-button>
            </form>

            <p class="event-hint mt-4">
                {!! __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . e($version_available) . '/eventschedule.zip" class="hover:underline">eventschedule.zip</a>']) !!}
            </p>
        @elseif ($version_available === null)
            @include('profile.partials.notice', ['noticeText' => __('messages.version_check_failed'), 'noticeClass' => 'mt-4 mb-0'])
        @else
            <p class="mt-4"><span class="event-status is-on">{{ __('messages.up_to_date') }}</span></p>
        @endif
    </div>
</section>
