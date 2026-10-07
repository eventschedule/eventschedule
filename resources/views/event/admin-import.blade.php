<x-app-admin-layout>

    {{-- The way back is the schedule, by name. It carries js-back-btn so the script below can ask
         before unsaved events are left behind, and send someone who has just added events to the
         page that shows them. --}}
    <x-page-header :title="__('messages.import_events')" :image="$role->profile_image_url ?: null"
        :back="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])" :back-label="$role->name" back-class="js-back-btn" />

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
                if (hasUnsavedImportChanges() && !confirm(@json(__('messages.unsaved_changes_warning')))) {
                    e.preventDefault();
                    return;
                }
                // With events added, the way back means "show me": through the route that counts
                // them and shows the panel. `addedAny` and not the list of saved rows: "Clear"
                // empties that list, and what was added is still added. Otherwise the link goes
                // where it says, the schedule: it used to be history.back(), which after a trip
                // to Google's permission screen was Google.
                var app = window.__importApp;
                if (app && app.addedAny) {
                    e.preventDefault();
                    window.location.href = @json(route('event.import_done', ['subdomain' => $role->subdomain]));
                }
                return;
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