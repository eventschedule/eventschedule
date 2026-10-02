@php
    // Shared by the post-signup chooser (/getting-started) and the dashboard's zero-schedule panel.
    // Below lg the cards stack, capped so a lone card does not stretch across a wide pane.
    $cardHeading = $cardHeading ?? 'h2';

    // Literal class strings, so Tailwind's scanner can see every one of them.
    $cards = [
        'talent' => [
            'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
            'iconTile' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'rule' => 'bg-blue-50/80 dark:bg-blue-500/[0.08]',
            'check' => 'text-blue-500 dark:text-blue-400',
            'cta' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300 group-hover:bg-blue-600 group-hover:text-white group-focus-visible:bg-blue-600 group-focus-visible:text-white',
        ],
        'venue' => [
            'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
            'iconTile' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
            'rule' => 'bg-sky-50/80 dark:bg-sky-500/[0.08]',
            'check' => 'text-sky-600 dark:text-sky-400',
            'cta' => 'bg-sky-50 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300 group-hover:bg-sky-700 group-hover:text-white group-focus-visible:bg-sky-700 group-focus-visible:text-white',
        ],
        'curator' => [
            'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            'iconTile' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
            'rule' => 'bg-indigo-50/80 dark:bg-indigo-500/[0.08]',
            'check' => 'text-indigo-500 dark:text-indigo-400',
            'cta' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 group-hover:bg-indigo-600 group-hover:text-white group-focus-visible:bg-indigo-600 group-focus-visible:text-white',
        ],
    ];
@endphp

<div class="schedule-type-grid grid grid-cols-1 gap-4 mx-auto max-w-md lg:max-w-none lg:grid-cols-3">
    @foreach ($cards as $type => $card)
        {{-- The whole card is the link. Named by its title alone, so a screen reader announces
             "Talent, link" rather than reading every feature line as the link text. --}}
        <a href="{{ route('new', ['type' => $type]) }}"
           data-type="{{ $type }}"
           aria-labelledby="schedule-type-{{ $type }}"
           aria-describedby="schedule-type-{{ $type }}-for"
           class="schedule-type-card group ap-card rounded-2xl p-6 flex flex-col focus:outline-none">

            <div class="flex items-center gap-3">
                <span class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $card['iconTile'] }}" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                </span>
                <{{ $cardHeading }} id="schedule-type-{{ $type }}" class="text-xl font-bold text-gray-900 dark:text-white">{{ __('messages.' . $type) }}</{{ $cardHeading }}>
            </div>
            <p id="schedule-type-{{ $type }}-for" class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.' . $type . '_best_for') }}</p>

            {{-- The one line that tells the three types apart. The {type}_footer strings carry
                 <b><i> around the part that differs; app.css turns that into the accent colour.
                 Translation overrides are sanitized on save (TranslationOverrideTest), so this
                 stays a raw echo. --}}
            <p class="schedule-type-rule mt-4 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-300 {{ $card['rule'] }}">{!! __('messages.' . $type . '_footer') !!}</p>

            <ul class="mt-4 mb-6 space-y-2.5">
                @foreach ([1, 2, 3] as $n)
                    <li class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 {{ $card['check'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ __('messages.' . $type . '_feature_' . $n) }}
                    </li>
                @endforeach
            </ul>

            <span class="mt-auto flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-base font-semibold transition-all duration-200 {{ $card['cta'] }}" aria-hidden="true">
                {{ __('messages.get_started_cta') }}
                <svg class="w-4 h-4 rtl:rotate-180 transition-transform duration-200 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </span>
        </a>
    @endforeach
</div>
