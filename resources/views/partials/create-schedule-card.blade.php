{{-- "Run events of your own?" with the three kinds of schedule, for somebody who runs none: the
     dashboard of a person who only follows or holds tickets, and the Following page, where a
     confirmed subscriber lands after setting a password. Its own menu of the three kinds, not a
     link to /getting-started: that page sends anyone who holds a ticket straight back to the
     dashboard. $menuId keeps two cards' menus apart. --}}
<section class="ap-card rounded-xl p-4 sm:p-5 flex flex-wrap items-center gap-3">
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.dash_own_events_title') }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.dash_own_events_body') }}</p>
    </div>
    <div class="relative inline-block text-start">
        {{-- A button with <x-secondary-link>'s classes: it opens a menu, so it cannot be the link. --}}
        <button type="button" class="popup-toggle ap-secondary-btn inline-flex items-center justify-center gap-1.5 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-popup-target="{{ $menuId }}" aria-expanded="false">
            {{ __('messages.create_schedule') }}
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
        </button>
        <div id="{{ $menuId }}" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-56 max-w-[calc(100vw-2rem)] rounded-md ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none">
            <div class="py-1" data-popup-target="{{ $menuId }}">
                @foreach (['talent', 'venue', 'curator'] as $type)
                    <a href="{{ route('new', ['type' => $type]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10">{{ __('messages.'.$type) }}</a>
                @endforeach
            </div>
        </div>
    </div>
</section>
