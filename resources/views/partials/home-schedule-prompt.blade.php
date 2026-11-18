{{-- Tells the admin of a selfhost install with a single schedule that the site's own address can
     show it (App\Support\HomeSchedule::promptFor()). Dismissible for good, per admin.

     Says outright where signing in moves to, because that is the one thing the option takes away:
     the bare address stops opening the sign-in page.

     Informational blue, like the network prompt: an invitation, not a problem. --}}
@php
    $homeRoot = rtrim(url('/'), '/');
@endphp
<div v-pre>
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
        <div class="flex items-start gap-3">
            <svg aria-hidden="true" class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.home_schedule_prompt_title', ['name' => $role->name]) }}
                </p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('messages.home_schedule_prompt_body', ['url' => $homeRoot]) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('messages.home_schedule_login_note', ['url' => route('login')]) }}
                </p>

                {{-- Wraps on mobile. Forward action last. --}}
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <form method="POST" action="{{ route('home.home_schedule_prompt_dismiss') }}">
                        @csrf
                        <button type="submit"
                                aria-label="{{ __('messages.home_schedule_prompt_dismiss_label') }}"
                                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">
                            {{ __('messages.dismiss') }}
                        </button>
                    </form>

                    {{-- nexus_url, not marketing_url: the docs live on the nexus, and marketing_url is
                         what a white-labeled operator points at their own site. --}}
                    <x-link href="{{ rtrim(config('app.nexus_url'), '/') }}/docs/selfhost/admin#settings-home-schedule" target="_blank"
                            class="text-xs font-medium">
                        {{ __('messages.learn_more') }}
                    </x-link>

                    {{-- ?card= survives the admin password confirm, where a #fragment does not. --}}
                    <x-brand-link href="{{ route('admin.settings', ['card' => 'home-schedule']) }}">
                        {{ __('messages.federation_prompt_open_settings') }}
                    </x-brand-link>
                </div>
            </div>
        </div>
    </div>
</div>
