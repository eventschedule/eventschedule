@php
    // Shared by the post-signup chooser (/getting-started) and the dashboard's zero-schedule panel.
    // One choice and one Continue: a GET form to /getting-started, whose ?type= forwards to
    // /new/{type} (HomeController::gettingStarted()), so it works with no script at all. What is
    // selected, the market switch under Venue and the Continue label are all drawn by CSS from
    // the checked radio (.st-chooser in app.css).
    $cardHeading = $cardHeading ?? 'h2';
    $skipUrl = $skipUrl ?? null;

    // What tells the three types apart: whose events, and at which venues. The primary card says
    // it as a two-cell strip (Events | Venues); the two alternatives as one line under their
    // names ("Anyone's events · your venue"), as the 2026-11 design draws them. The values are
    // separate keys because some languages agree them with the noun.
    $options = [
        'talent' => ['events' => 'schedule_type_events_yours', 'venues' => 'schedule_type_venues_anywhere'],
        'venue' => ['events' => 'schedule_type_events_anyones', 'venues' => 'schedule_type_venues_yours', 'line_venue' => 'schedule_type_line_your_venue'],
        'curator' => ['events' => 'schedule_type_events_anyones', 'venues' => 'schedule_type_venues_anywhere', 'line_venue' => 'schedule_type_line_any_venue'],
    ];
    // The line's events half is a sentence with the bold word in it; split around the placeholder
    // rather than printing the translation as HTML, which an operator's override could carry.
    [$lineBefore, $lineAfter] = array_pad(explode(':whose', __('messages.schedule_type_line_events'), 2), 2, '');
@endphp

{{-- The standard schedule ("Event schedule", `talent` in the code) is the one most people want,
     so it is the wide option on top and is chosen to begin with; Venue and Curator are the two
     alternatives under it. The word "talent" is never shown: ScheduleTypeLabel names the type. --}}
<form method="GET" action="{{ route('getting-started') }}" class="st-chooser w-full max-w-[57.5rem]">
    <fieldset>
        <legend class="sr-only">{{ __('messages.schedule_type_question_for') }}</legend>

        @foreach ($options as $type => $option)
            @php($primary = $type === 'talent')
            @if ($type === 'venue')
                <p class="mt-7 mb-3 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.schedule_type_alternatives') }}</p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @endif

            <div class="st-card {{ $primary ? 'st-card-primary ap-card ![box-shadow:var(--ap-shadow-lift)]' : 'st-card-alt' }}" data-type="{{ $type }}">
                @if ($primary)
                    <span class="st-badge">{{ __('messages.schedule_type_most_common') }}</span>
                @endif

                <input type="radio" id="schedule-type-choice-{{ $type }}" name="type" value="{{ $type }}" class="sr-only" {{ $primary ? 'checked' : '' }}
                    aria-labelledby="schedule-type-{{ $type }}" aria-describedby="schedule-type-{{ $type }}-for">
                {{-- The whole card is the radio's label, laid over the content rather than around it:
                     a heading inside a <label> is not valid HTML. The radio is named by its heading. --}}
                <label for="schedule-type-choice-{{ $type }}" class="st-hit" aria-hidden="true"></label>

                <div class="st-option {{ $primary ? 'p-6 sm:p-8 md:flex md:flex-wrap md:gap-x-10 md:gap-y-7' : 'px-[1.125rem] py-4' }}">
                    <span class="st-radio {{ $primary ? 'st-radio-lg' : '' }}" aria-hidden="true"></span>

                    <div class="flex min-w-0 flex-col {{ $primary ? 'gap-5 md:flex-[1_1_20rem]' : 'gap-3' }}">
                        <div class="flex flex-col {{ $primary ? 'gap-2 pe-10' : 'gap-[0.1875rem] pe-8' }}">
                            <{{ $cardHeading }} id="schedule-type-{{ $type }}" class="{{ $primary ? 'text-2xl sm:text-[1.75rem] leading-tight tracking-tight font-bold' : 'text-base font-semibold tracking-tight' }} text-gray-900 dark:text-white">{{ \App\Utils\ScheduleTypeLabel::for($type) }}</{{ $cardHeading }}>
                            <p id="schedule-type-{{ $type }}-for" class="{{ $primary ? 'text-base leading-[1.45]' : 'text-[0.8125rem] leading-[1.45]' }} text-gray-600 dark:text-gray-400">{{ __('messages.' . $type . '_best_for') }}</p>
                        </div>

                        @if ($primary)
                        <span class="st-strip max-w-[21.25rem]">
                            <span class="st-cell">
                                <span class="st-cell-label">{{ __('messages.events') }}</span>
                                <span class="st-cell-value {{ $option['events'] === 'schedule_type_events_yours' ? 'is-yours' : '' }}">{{ __('messages.' . $option['events']) }}</span>
                            </span>
                            <span class="st-cell">
                                <span class="st-cell-label">{{ __('messages.venues') }}</span>
                                <span class="st-cell-value {{ $option['venues'] === 'schedule_type_venues_yours' ? 'is-yours' : '' }}">{{ __('messages.' . $option['venues']) }}</span>
                            </span>
                        </span>
                        @else
                        <p class="st-line text-[0.8125rem] leading-[1.45] text-gray-600 dark:text-gray-400">{{ $lineBefore }}<span class="font-semibold text-gray-900 dark:text-white">{{ __('messages.schedule_type_line_anyones') }}</span>{{ $lineAfter }} <span aria-hidden="true">&middot;</span> <span class="font-semibold text-gray-900 dark:text-white">{{ __('messages.' . $option['line_venue']) }}</span></p>
                        @endif
                    </div>

                    <ul class="flex flex-col justify-center {{ $primary ? 'mt-6 gap-3 md:mt-0 md:min-w-0 md:flex-[1_1_16.25rem]' : 'mt-3 gap-1.5' }}">
                        @foreach ([1, 2, 3] as $n)
                            <li class="flex items-start {{ $primary ? 'gap-3 text-base text-gray-700 dark:text-gray-300' : 'gap-2 text-[0.8125rem] leading-[1.4] text-gray-600 dark:text-gray-400' }}">
                                <svg class="{{ $primary ? 'w-5 h-5 text-[var(--brand-blue)]' : 'w-4 h-4 text-gray-500 dark:text-gray-500' }} mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                <span>{{ __('messages.' . $type . '_feature_' . $n) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($type === 'venue')
                    {{-- A market, a fair or an expo is a venue with a vendor map. Offered only once
                         Venue is chosen (app.css shows it); the server reads it only beside
                         type=venue, so a switch left on under another choice is ignored. Then the
                         venue form opens and, once saved, the map's setup ("When is the market?"). --}}
                    <div class="st-market relative z-10 px-[1.125rem] pb-4">
                        <div class="flex items-start gap-3 border-t border-gray-200 dark:border-white/10 pt-3">
                            <label class="relative mt-0.5 h-6 w-11 flex-shrink-0 cursor-pointer">
                                <input type="checkbox" id="schedule-type-market" name="vendor_map" value="market" class="sr-only peer"
                                    aria-describedby="schedule-type-market-lead">
                                <span class="block h-6 w-11 rounded-full bg-gray-300 dark:bg-gray-600 transition-colors peer-checked:bg-[var(--brand-button-bg)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand-blue)] peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-gray-800"></span>
                                <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200 ltr:left-0.5 rtl:right-0.5 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></span>
                            </label>
                            <label for="schedule-type-market" class="min-w-0 cursor-pointer">
                                <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.vm_type_card_title') }}</span>
                                <span id="schedule-type-market-lead" class="block text-sm text-gray-500 dark:text-gray-400">{{ __('messages.vm_type_card_lead') }}</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            @if ($type === 'curator')
                </div>
            @endif
        @endforeach
    </fieldset>

    <div class="mt-9 flex flex-wrap items-center gap-x-5 gap-y-2">
        <button type="submit" class="inline-flex min-h-[3.25rem] items-center gap-2.5 rounded-xl bg-[var(--brand-button-bg)] px-6 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
            {{-- One label per choice, shown by app.css from the checked radio. The type's name is
                 lower-cased inside the sentence ("Continue with venue"), except in German, where
                 every noun keeps its capital. --}}
            @foreach (array_keys($options) as $type)
                @php($typeName = \App\Utils\ScheduleTypeLabel::for($type))
                <span class="st-continue" data-for="{{ $type }}">{{ __('messages.schedule_type_continue_with', ['type' => app()->getLocale() === 'de' ? $typeName : mb_strtolower($typeName)]) }}</span>
            @endforeach
            <span aria-hidden="true" class="inline-block rtl:rotate-180">&rarr;</span>
        </button>
        @if ($skipUrl)
            <a href="{{ $skipUrl }}" class="px-1 py-3 text-[0.9375rem] font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                {{ __('messages.skip_for_now') }}
            </a>
        @endif
    </div>
</form>
