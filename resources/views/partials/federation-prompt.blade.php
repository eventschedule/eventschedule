{{-- Suggests turning federation on, once the install has an event that would actually
     be shared. Shown to admins only, since enabling it is an admin action, and
     dismissible permanently per user.

     Included from both the dashboard and the schedule tab, because creating an event
     redirects to the schedule page rather than the dashboard - a dashboard-only banner
     would never appear at the moment the trigger fires.

     Informational blue rather than the amber warning panel: this is an invitation, not
     a problem, and on curator schedules it deliberately sits below the real warnings. --}}
<div class="pb-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3" v-pre>
        <div class="flex items-start gap-3">
            {{-- Same globe as the settings card and the docs page, so the three surfaces
                 read as one feature. --}}
            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.federation_prompt_title') }}
                </p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('messages.federation_prompt_body') }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('messages.federation_prompt_reassurance') }}
                </p>

                {{-- Wraps on mobile. Forward action last. --}}
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <form method="POST" action="{{ route('home.federation_prompt_dismiss') }}">
                        @csrf
                        <button type="submit"
                                aria-label="{{ __('messages.federation_prompt_dismiss_label') }}"
                                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">
                            {{ __('messages.dismiss') }}
                        </button>
                    </form>

                    {{-- A middle path that is not a commitment. Without it the banner is a
                         bare choice between an irreversible dismissal and opting in.

                         Built from nexus_url, NOT marketing_url: the docs routes only
                         exist on the nexus, while marketing_url is what a white-labeled
                         operator points at their own site - so marketing_url would 404
                         for exactly the installs this feature is aimed at. --}}
                    <a href="{{ rtrim(config('app.nexus_url'), '/') }}/docs/selfhost/federation" target="_blank" rel="noopener"
                       class="text-xs font-medium text-blue-700 dark:text-blue-300 underline hover:no-underline">
                        {{ __('messages.learn_more') }}
                    </a>

                    {{-- Not "Enable": the admin middleware requires a password confirm, so
                         this opens the settings page - where the preview of exactly what
                         would be shared is shown before anything is turned on. --}}
                    <x-brand-link href="{{ route('admin.settings') }}#federation">
                        {{ __('messages.federation_prompt_open_settings') }}
                    </x-brand-link>
                </div>
            </div>
        </div>
    </div>
</div>
