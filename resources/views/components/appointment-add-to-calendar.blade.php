@props(['event', 'sale', 'role', 'primary' => false])

{{--
    Add-to-calendar menu for a confirmed booking.

    A <details> disclosure rather than the schedule page's dropdown: that one's .gp-dropdown styling
    and its toggle JS are both local to role/show-guest.blade.php, so reusing it would mean copying
    the CSS and the script. <details> needs no JS at all, is keyboard and screen-reader accessible by
    default, and avoids adding a Vue mount to a page that renders user-controlled text (which would
    then need v-pre on the type name, event name, address and URL).
--}}
@php
    $icalUrl = route('appointments.ical', [
        'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
        'secret' => $sale->secret,
    ]);
    $itemClass = 'flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors';
@endphp

<details class="group relative inline-block">
    <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-lg px-4 py-3 text-base font-semibold transition-all duration-200 {{ $primary ? 'es-accent-fill' : 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
        {{ $role->customLabel('add_to_calendar') }}
        <svg class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </summary>

    <div class="absolute z-20 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 {{ $role->isRtl() ? 'end-0' : 'start-0' }}">
        <a href="{{ \App\Utils\IcsUtils::googleUrl($event, $sale) }}" target="_blank" rel="noopener noreferrer" class="{{ $itemClass }}">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
            </svg>
            Google Calendar
        </a>
        <a href="{{ \App\Utils\IcsUtils::outlookUrl($event, $sale) }}" target="_blank" rel="noopener noreferrer" class="{{ $itemClass }}">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21.17 3.25H8.83a.83.83 0 00-.83.83v2.09L2.4 7.36a.5.5 0 00-.4.49v8.3a.5.5 0 00.4.49L8 17.83v2.09c0 .46.37.83.83.83h12.34c.46 0 .83-.37.83-.83V4.08a.83.83 0 00-.83-.83zM6.16 14.6c-1.4 0-2.36-1.09-2.36-2.6s.96-2.6 2.36-2.6 2.35 1.09 2.35 2.6-.95 2.6-2.35 2.6zm14.5 4.4H9.5v-1.5h3.25v-2h-3.25v-1.5h3.25v-2H9.5v-1.5h3.25v-2H9.5V4.75h11.16z" />
            </svg>
            Outlook
        </a>
        {{-- Covers Apple Calendar and every desktop client. --}}
        <a href="{{ $icalUrl }}" class="{{ $itemClass }}">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
            </svg>
            {{ __('messages.appointments_calendar_file') }}
        </a>
    </div>
</details>
