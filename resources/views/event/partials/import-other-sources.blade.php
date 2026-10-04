{{-- The other places events can come from, shown under the import box and, when a read fails,
     as the way out of it. Included inside the #event-import-app Vue mount: it echoes no user text,
     and must keep it that way. --}}
<div class="grid grid-cols-1 gap-4">
    @if ($role->isPro())
        <a href="{{ route('event.show_import_eventbrite', ['subdomain' => $role->subdomain]) }}"
           class="js-leave-import ap-card group flex items-center gap-3 rounded-xl p-4 transition-all duration-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[#F05537]/10" aria-hidden="true">
                <svg class="h-5 w-5 text-[#F05537]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.996 6.002l-1.477 4.533c-.124.378.1.77.497.868l5.14 1.28c.394.098.582.54.334.858-1.186 1.512-3.038 3.8-5.15 5.296-2.048 1.452-3.89 1.998-5.293 2.144-.396.04-.7-.32-.584-.7l1.478-4.85c.123-.4-.107-.81-.514-.908l-4.822-1.166c-.394-.096-.574-.54-.326-.858 1.172-1.504 3.018-3.8 5.128-5.294 2.048-1.452 3.888-1.998 5.29-2.146.397-.04.702.32.586.7-.044.056-.217.78-.287.943z"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.import_from_eventbrite') }}</span>
                <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ __('messages.eventbrite_import_description') }}</span>
            </span>
            <svg class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </a>
    @else
        <div class="ap-card flex items-center gap-3 rounded-xl p-4 opacity-60">
            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[#F05537]/10" aria-hidden="true">
                <svg class="h-5 w-5 text-[#F05537]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.996 6.002l-1.477 4.533c-.124.378.1.77.497.868l5.14 1.28c.394.098.582.54.334.858-1.186 1.512-3.038 3.8-5.15 5.296-2.048 1.452-3.89 1.998-5.293 2.144-.396.04-.7-.32-.584-.7l1.478-4.85c.123-.4-.107-.81-.514-.908l-4.822-1.166c-.394-.096-.574-.54-.326-.858 1.172-1.504 3.018-3.8 5.128-5.294 2.048-1.452 3.888-1.998 5.29-2.146.397-.04.702.32.586.7-.044.056-.217.78-.287.943z"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.import_from_eventbrite') }}
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        Pro
                    </span>
                </span>
                <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ __('messages.eventbrite_import_description') }}</span>
            </span>
        </div>
    @endif
</div>
