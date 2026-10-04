<x-app-admin-layout>

    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <!-- RTL Layout: Cancel button on left, title on right -->
            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>
            
            <div class="flex items-center text-end">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>
        @else
            <!-- LTR Layout: Title on left, cancel button on right -->
            <div class="flex items-center">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>
        @endif
    </div>

    {{-- The import before this one, while it can still be taken back. Outside the Vue mount. --}}
    @if (! empty($lastImportCount))
    <div class="ap-card mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl p-4">
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.import_last_run', ['count' => $lastImportCount]) }}</p>
        @include('event.partials.import-undo', ['count' => $lastImportCount])
    </div>
    @endif

    @include('event.import')

    <script {!! nonce_attr() !!}>
        function hasUnsavedImportChanges() {
            var app = window.__importApp;
            if (!app || !app.preview || !app.preview.parsed) return false;
            return app.preview.parsed.some(function(_, idx) { return !app.savedEvents[idx]; });
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.js-back-btn')) {
                if (hasUnsavedImportChanges() && !confirm(@json(__('messages.unsaved_changes_warning')))) return;
                // With events added, Back means "show me": through the route that counts them
                // and shows the panel. history.back() would land on whatever was open before,
                // and after a trip to Google that is Google.
                var app = window.__importApp;
                if (app && app.savedEvents && app.savedEvents.some(Boolean)) {
                    window.location.href = @json(route('event.import_done', ['subdomain' => $role->subdomain]));
                    return;
                }
                history.back();
            }
            // Leaving for another way to import throws the preview away, like Back.
            var leaving = e.target.closest('.js-leave-import');
            if (leaving) {
                if (hasUnsavedImportChanges() && !confirm(@json(__('messages.unsaved_changes_warning')))) {
                    e.preventDefault();
                }
            }
        });
    </script>

</x-app-admin-layout>