<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* Each button is its own form; the row lays out the buttons, not the forms. */
            .page-form-actions > form {
              display: contents;
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'app-update'])

    {{-- Selfhost only (the nexus deploys from git and answers 404 here). What is installed and
         what is out, as one strip like the other System pages; then what to do about it. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_app_update_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

        <div class="ap-card rounded-xl page-stats">
            <div class="page-stat">
                {{-- bdi + dir=ltr: a version is a left-to-right token even on an RTL page. --}}
                <div class="page-stat-value"><bdi dir="ltr">{{ $version_installed }}</bdi></div>
                <div class="page-stat-label">{{ __('messages.installed_version') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $update_available ? 'is-warn' : '' }}"><bdi dir="ltr">{{ $version_available ?? __('messages.unknown') }}</bdi></div>
                <div class="page-stat-label">{{ __('messages.latest_version') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ $last_checked_at ? $last_checked_at->diffForHumans() : __('messages.unknown') }}</div>
                <div class="page-stat-label">{{ __('messages.last_checked') }}</div>
            </div>
        </div>

        <x-page-card beside :title="__('messages.app_update')">
            <x-slot name="aside">
                @if ($update_available)
                    <span class="event-status is-warn">{{ __('messages.app_update_available') }}</span>
                @elseif ($version_available !== null)
                    <span class="event-status is-on">@lang('messages.up_to_date')</span>
                @endif
            </x-slot>

            <div class="page-form-fields">
                @if ($update_available)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {!! __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . e($version_available) . '/eventschedule.zip" class="event-link">eventschedule.zip</a>']) !!}
                    </p>

                    <x-page-notice tone="warn">{{ __('messages.app_update_backup_warning') }}</x-page-notice>
                @elseif ($version_available === null)
                    <x-page-notice tone="warn">{{ __('messages.version_check_failed') }}</x-page-notice>
                @endif
            </div>

            {{-- Checking again is the quiet button; updating, which replaces the application's
                 files, is the one that goes on and asks first (data-confirm, read by the layout's
                 one handler). --}}
            <div class="page-form-actions">
                <form method="POST" action="{{ route('admin.app_update.check') }}">
                    @csrf
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.check_for_updates') }}
                    </button>
                </form>

                @if ($update_available)
                <form method="POST" action="{{ route('admin.app_update.run') }}" data-confirm="{{ __('messages.confirm_app_update') }}">
                    @csrf
                    <x-brand-button type="submit" :disabled="is_demo_mode()">
                        {{ __('messages.update') }}
                    </x-brand-button>
                </form>
                @endif
            </div>
        </x-page-card>
    </div>

</x-app-admin-layout>
