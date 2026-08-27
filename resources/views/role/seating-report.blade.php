<x-app-admin-layout>
    @php
        $hash = \App\Utils\UrlUtils::encodeId($event->id);
        // The date is load-bearing: without it the back link and the CSV both resolve to the
        // series anchor, so a report for one night of a run exported another night's seats.
        $args = ['subdomain' => $subdomain, 'hash' => $hash, 'date' => $map->event_date];
        $occurrenceDates = $event->adminOccurrenceDates();
    @endphp

    {{-- Print CSS rather than a PDF library: this project has none, and the ticket page already
         establishes the pattern. Deliberately plain black on white when printed - a front-of-house
         sheet usually comes off a mono laser printer. --}}
    <style {!! nonce_attr() !!}>
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .print-plain { background: #fff !important; color: #000 !important; box-shadow: none !important; border-color: #999 !important; }
            .seat-map { page-break-inside: avoid; }
            table { font-size: 11px; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="ap-card print-plain rounded-xl p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $event->translatedName() }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($map->event_date)->translatedFormat('l, F j, Y') }}</p>
                    </div>
                    <div class="no-print flex flex-wrap items-center gap-3">
                        {{-- The view rides along, or changing night silently resets a door list
                             back to the whole house. --}}
                        <x-seating-date-picker
                            :action="route('box_office.report', ['subdomain' => $subdomain, 'hash' => $hash])"
                            :dates="$occurrenceDates"
                            :current="$map->event_date"
                            :view="$view ?? 'all'" />
                        <a href="{{ route('box_office.show', $args) }}"
                           class="text-sm font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.seating_box_office') }}</a>
                        {{-- Which rows the list carries. The map always shows the whole house; only
                             the table narrows, because that is the part that runs to thousands of
                             lines - a 2,000-seat room on a 40%-sold night printed 2,000 of them. --}}
                        @foreach (['taken' => 'seating_report_taken', 'names' => 'seating_report_names', 'all' => 'seating_report_all'] as $key => $label)
                            <a href="{{ route('box_office.report', $args + ['view' => $key]) }}"
                               class="text-sm font-medium {{ ($view ?? 'all') === $key ? 'text-gray-900 dark:text-gray-100 underline' : 'text-[var(--brand-blue)] hover:underline' }}">{{ __('messages.'.$label) }}</a>
                        @endforeach
                        {{-- A print artifact with no print button: staff had to know the browser
                             command. data-print is the app-wide delegated handler. --}}
                        <button type="button" data-print
                                class="text-sm font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.print') }}</button>
                        <a href="{{ route('box_office.report_csv', $args + ['view' => $view ?? 'all']) }}"
                           class="text-sm font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.seating_report_csv') }}</a>
                    </div>
                </div>

                {{-- How full, and WHERE the empty seats are. The sheet carried five raw integers,
                     so "the Circle is dead" was something a producer had to work out by hand. --}}
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $occupancy['percent'] }}%</span>
                        {{ __('messages.seating_percent_sold') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $occupancy['sold'] }} / {{ $occupancy['total'] }})</span>
                    </p>
                    @if (count($occupancy['sections']) > 1)
                        <ul class="mt-1 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            @foreach ($occupancy['sections'] as $sectionStat)
                                <li>{{ $sectionStat['name'] }}: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $sectionStat['percent'] }}%</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm">
                    @foreach (['sold' => 'seating_count_sold', 'blocked' => 'seating_count_blocked', 'held' => 'seating_count_held', 'available' => 'seating_legend_available'] as $key => $label)
                        <div class="flex gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('messages.'.$label) }}:</dt>
                            <dd class="font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $counts[$key] ?? 0 }}</dd>
                        </div>
                    @endforeach
                    <div class="flex gap-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('messages.seating_seats') }}:</dt>
                        <dd class="font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $counts['total'] }}</dd>
                    </div>
                </dl>
            </div>

            {{-- One block per level. Levels are separate spaces and each one's first section starts
                 at the same origin, so drawing them together put the balcony on top of the stalls.
                 The interactive maps switch between levels; paper stacks them. --}}
            @foreach ($levels as $level)
                <div class="ap-card print-plain rounded-xl p-4 seat-map">
                    @if (count($levels) > 1 && $level['name'])
                        <h2 class="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $level['name'] }}</h2>
                    @endif
                    {{-- Status is carried by SHAPE, not colour: this sheet is usually printed in
                         black and white, where four shades of grey are four identical circles. --}}
                    <svg viewBox="{{ $level['viewBox'] }}" class="w-full" style="max-height: 26rem;" role="img"
                         aria-label="{{ __('messages.seating_map_label') }}">
                        <defs>
                            <pattern id="rptBlocked" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                <line x1="0" y1="0" x2="0" y2="4" stroke="#000" stroke-width="1.4" />
                            </pattern>
                        </defs>
                        {{-- Before the seats so it can never print over one. Outlined rather than
                             filled: this sheet goes through a mono laser printer, where a solid
                             block of grey costs toner and reads as a smudge. --}}
                        @foreach ($level['decorations'] ?? [] as $decoration)
                            <g transform="translate({{ $decoration['x'] }} {{ $decoration['y'] }}) rotate({{ $decoration['rotation'] }})">
                                @if ($decoration['kind'] === 'stage')
                                    <rect width="{{ $decoration['width'] }}" height="{{ $decoration['height'] }}" rx="4"
                                          fill="none" stroke="#111827" stroke-width="1.2" />
                                @endif
                                <text x="{{ $decoration['width'] / 2 }}" y="{{ $decoration['height'] / 2 }}"
                                      text-anchor="middle" dy="4"
                                      font-size="{{ $decoration['kind'] === 'stage' ? 13 : 11 }}"
                                      fill="#111827" letter-spacing="{{ $decoration['kind'] === 'stage' ? 2 : 0 }}">{{ $decoration['label'] }}</text>
                            </g>
                        @endforeach
                        @foreach ($level['drawn'] as $seat)
                            <g transform="translate({{ $seat['x'] }} {{ $seat['y'] }})">
                                <circle r="9" fill="{{ $seat['state'] === 'sold' ? '#111827' : ($seat['state'] === 'blocked' ? 'url(#rptBlocked)' : '#fff') }}"
                                        stroke="#111827" stroke-width="1.2"
                                        stroke-dasharray="{{ $seat['state'] === 'held' ? '3 2' : '' }}" />
                                @if ($seat['state'] === 'sold')
                                    <text text-anchor="middle" dy="3.5" font-size="9" fill="#fff">&#10005;</text>
                                @elseif ($seat['kind'] === 'wheelchair')
                                    <text text-anchor="middle" dy="4" font-size="10" fill="#111827">&#9855;</text>
                                @elseif ($seat['label'])
                                    <text text-anchor="middle" dy="3.5" font-size="8" fill="#111827">{{ $seat['label'] }}</text>
                                @endif
                            </g>
                        @endforeach
                    </svg>

                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400 print-plain">
                        <span>&#10005; {{ __('messages.seating_count_sold') }}</span>
                        <span>&#9698; {{ __('messages.seating_count_blocked') }}</span>
                        <span>&#9711; {{ __('messages.seating_legend_available') }}</span>
                        <span>&#9855; {{ __('messages.seating_kind_wheelchair') }}</span>
                    </div>
                </div>
            @endforeach

            {{-- The whole run at a glance. Everything in this feature is keyed to one date, so
                 comparing nights meant opening thirty consoles - and the docs named it as a limit,
                 "the report is per date, not per run". --}}
            @if (! empty($run))
                <div class="ap-card print-plain rounded-xl p-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.seating_run_occupancy') }}</h2>
                    <ul class="mt-3 space-y-1.5">
                        @foreach ($run as $night)
                            <li class="flex items-center gap-3 text-sm">
                                <a href="{{ route('box_office.report', ['subdomain' => $subdomain, 'hash' => $hash, 'date' => $night['date'], 'view' => $view ?? 'all']) }}"
                                   class="w-32 shrink-0 {{ $night['current'] ? 'font-semibold text-gray-900 dark:text-gray-100' : 'text-[var(--brand-blue)] hover:underline' }}">
                                    {{ \Carbon\Carbon::parse($night['date'])->translatedFormat('D, M j') }}
                                </a>
                                <span class="h-2 flex-1 max-w-xs rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden print-plain">
                                    <span class="block h-full rounded-full bg-[var(--brand-button-bg)] print:bg-slate-600" style="width: {{ $night['percent'] }}%"></span>
                                </span>
                                <span class="w-24 shrink-0 tabular-nums text-gray-600 dark:text-gray-400">
                                    {{ $night['percent'] }}% <span class="text-xs text-gray-400">({{ $night['sold'] }})</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="ap-card print-plain rounded-xl p-4 overflow-x-auto">
                {{-- Printed too: a filtered sheet that does not say so reads as a smaller house. --}}
                @if (($view ?? 'all') !== 'all')
                    <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.seating_report_filtered', ['count' => count($rows)]) }}</p>
                @endif
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <th class="py-2 pe-4">{{ __('messages.seating_section') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.seating_rows') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.seating_seats') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.status') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.ticket') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.name') }}</th>
                            <th class="py-2 pe-4">{{ __('messages.seating_arrived') }}</th>
                            <th class="py-2">{{ __('messages.seating_internal_note') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($rows as $row)
                            <tr class="text-gray-800 dark:text-gray-200">
                                <td class="py-1.5 pe-4">{{ $row['section'] }}</td>
                                <td class="py-1.5 pe-4">{{ $row['row'] }}</td>
                                <td class="py-1.5 pe-4">{{ $row['seat'] }}</td>
                                <td class="py-1.5 pe-4">{{ $row['status'] }}</td>
                                <td class="py-1.5 pe-4">{{ $row['ticket'] ?? '' }}</td>
                                <td class="py-1.5 pe-4">{{ $row['name'] }}</td>
                                {{-- A tick if they are in, an empty box if not: this sheet is
                                     carried on a clipboard, and a column you can mark by hand is
                                     the whole point of printing it. --}}
                                <td class="py-1.5 pe-4">{{ ($row['state'] ?? '') === 'sold' ? (($row['arrived'] ?? false) ? '☑' : '☐') : '' }}</td>
                                <td class="py-1.5 text-gray-500 dark:text-gray-400">{{ $row['note'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-admin-layout>
