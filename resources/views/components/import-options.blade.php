@props([
    'role',
    // band: above the first-event form, with "or add one by hand" leading into it. schedule: above
    // the calendar of a schedule with no events, whose own Add Event is the way by hand.
    'variant' => 'band',
])

@php
    // Which sources, in which order: App\Utils\ImportOptions, which the import page asks too.
    $tiles = \App\Utils\ImportOptions::for($role, auth()->user());

    // Literal class strings, so Tailwind's scanner can see every one of them. Three across only
    // from xl: between 768px and the sidebar's widths a third of the row left each tile's text a
    // column of 80 to 115px, and two across would leave a cell of the grid empty.
    $grid = [1 => 'max-w-xl', 2 => 'md:grid-cols-2', 3 => 'xl:grid-cols-3'][count($tiles)] ?? 'xl:grid-cols-3';
    $tints = [
        'link' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        'flyer' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'google' => 'bg-gray-100 dark:bg-gray-700',
    ];
    $icons = [
        'link' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'flyer' => 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    ];
@endphp

{{-- v-pre: the band sits inside the event form's Vue mount. It prints no user text, and links
     only, so nothing in it is Vue's to compile. --}}
<section class="import-options {{ $variant === 'band' ? 'mb-2 mt-2' : 'mb-6' }}" aria-labelledby="import-{{ $variant }}-title" v-pre>
    <h3 id="import-{{ $variant }}-title" class="mb-3 text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.import_band_title') }}</h3>

    <div class="import-options-grid grid grid-cols-1 gap-4 {{ $grid }}">
        @foreach ($tiles as $tile)
            {{-- The whole tile is the link, named by its title. --}}
            <a href="{{ $tile['href'] }}" data-import-source="{{ $tile['key'] }}"
               aria-labelledby="import-{{ $variant }}-{{ $tile['key'] }}" aria-describedby="import-{{ $variant }}-{{ $tile['key'] }}-for"
               class="ap-card group flex min-h-[56px] items-center gap-3 rounded-xl p-4 text-start transition-all duration-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $tints[$tile['key']] }}" aria-hidden="true">
                    @if ($tile['key'] === 'google')
                    {{-- The mark on the button they signed up with. --}}
                    <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    @else
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$tile['key']] }}" />
                    </svg>
                    @endif
                </span>
                {{-- break-words: a long word in one language ("Veranstaltungsseite") must not run
                     out of a narrow tile. --}}
                <span class="min-w-0 flex-1 break-words">
                    <span id="import-{{ $variant }}-{{ $tile['key'] }}" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tile['title'] }}</span>
                    <span id="import-{{ $variant }}-{{ $tile['key'] }}-for" class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $tile['description'] }}</span>
                </span>
                <svg class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @endforeach
    </div>

    @if ($variant === 'band')
    {{-- The other way in, which is the form below. --}}
    <div class="mt-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.import_band_by_hand') }}</span>
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
    </div>
    @endif
</section>
