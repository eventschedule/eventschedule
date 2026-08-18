<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'app-update'])

        @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="ap-card rounded-xl shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" />
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.installed_version')</span>
                </div>
                {{-- bdi + dir=ltr: a version is a left-to-right token even on an RTL page. --}}
                <div class="text-2xl font-bold text-gray-900 dark:text-white"><bdi dir="ltr">{{ $version_installed }}</bdi></div>
            </div>

            <div class="ap-card rounded-xl shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 {{ $update_available ? 'text-amber-500' : 'text-green-500' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.latest_version')</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    <bdi dir="ltr">{{ $version_available ?? __('messages.unknown') }}</bdi>
                </div>
            </div>

            <div class="ap-card rounded-xl shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.last_checked')</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $last_checked_at ? $last_checked_at->diffForHumans() : __('messages.unknown') }}
                </div>
            </div>
        </div>

        {{-- Status + actions --}}
        <div class="ap-card rounded-xl shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.app_update')</h3>

            @if ($update_available)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {!! __('messages.app_update_tip', ['link' => '<a href="https://github.com/eventschedule/eventschedule/releases/download/' . e($version_available) . '/eventschedule.zip" class="hover:underline">eventschedule.zip</a>']) !!}
                </p>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 mb-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.app_update_backup_warning')</p>
                    </div>
                </div>
            @elseif ($version_available === null)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">@lang('messages.version_check_failed')</p>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4"><b>@lang('messages.up_to_date')</b></p>
            @endif

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.app_update.check') }}">
                    @csrf
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        @lang('messages.check_for_updates')
                    </button>
                </form>

                @if ($update_available)
                <form method="POST" action="{{ route('admin.app_update.run') }}" class="js-confirm-form" data-confirm="{{ __('messages.confirm_app_update') }}">
                    @csrf
                    <x-brand-button type="submit" :disabled="is_demo_mode()">
                        @lang('messages.update')
                    </x-brand-button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });
    </script>

</x-app-admin-layout>
