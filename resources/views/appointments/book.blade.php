<x-app-guest-layout :role="$role" :page-title="__('messages.appointments')">
    @php
        $accent = $role->accent_color ?? '#4E81FA';
        $accentOnLight = \App\Utils\ColorUtils::readableAccentColor($accent, '#ffffff', '#111827');
        $accentOnDark = \App\Utils\ColorUtils::readableAccentColor($accent, '#252526', '#ffffff');

        // Faint accent tint for the duration chip, mixed here rather than with CSS color-mix() -
        // nothing else in the app relies on that function.
        $hex = ltrim($accent, '#');
        $hex = strlen($hex) === 3 ? $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2] : $hex;
        $accentTint = strlen($hex) === 6
            ? 'rgba('.hexdec(substr($hex, 0, 2)).', '.hexdec(substr($hex, 2, 2)).', '.hexdec(substr($hex, 4, 2)).', 0.12)'
            : 'transparent';

        $locationLabel = fn ($type) => match ($type->location_type) {
            'online' => __('messages.online'),
            'phone' => __('messages.appointments_phone_call'),
            default => __('messages.appointments_in_person'),
        };
    @endphp

    <style {!! nonce_attr() !!}>
        #appt-chooser { --es-accent: {{ $accent }}; --es-accent-readable: {{ $accentOnLight }}; --es-accent-tint: {{ $accentTint }}; }
        .dark #appt-chooser { --es-accent-readable: {{ $accentOnDark }}; }
        #appt-chooser .es-type-card { border-inline-start: 4px solid var(--es-accent-readable); }
        #appt-chooser .es-type-card:hover { border-color: var(--es-accent-readable); }
        #appt-chooser a:focus-visible { outline: 2px solid var(--es-accent-readable); outline-offset: 2px; }
    </style>

    {{-- Wider, with the types two-up: showBook() redirects when there is only one type, so this page
         always has at least two cards and a single column left half the width empty. --}}
    <div id="appt-chooser" class="max-w-4xl mx-auto px-4 py-10">
        {{-- Everything sits on an opaque panel. The schedule background defaults to a photo or a
             random gradient and the guest layout pins body text to a dark gray, so content rendered
             straight onto that background is unreadable half the time. Same approach as the schedule
             page's own content panel. --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $role->customLabel('book_a_time') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('messages.appointments_with', ['schedule' => $role->name]) }}</p>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($types as $type)
                    @php
                        $priceLabel = $type->isFree()
                            ? __('messages.free')
                            : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code);
                        // Self-describing out of context: a screen reader reading the link list alone
                        // would otherwise only hear the type name.
                        $ariaLabel = trim($type->name.' - '.$type->duration_minutes.' '.__('messages.minutes').' - '.$priceLabel);
                    @endphp
                    <a href="{{ route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]) }}"
                       aria-label="{{ $ariaLabel }}"
                       class="es-type-card flex flex-col p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 hover:shadow-md transition-all duration-200">
                        <h2 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $type->name }}</h2>
                        @if ($type->description)
                            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $type->description }}</p>
                        @endif
                        <div class="mt-auto pt-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" style="color: var(--es-accent-readable); background-color: var(--es-accent-tint)">
                                {{ $type->duration_minutes }} {{ __('messages.minutes') }}
                            </span>
                            <span>{{ $priceLabel }}</span>
                            <span aria-hidden="true">&middot;</span>
                            <span>{{ $locationLabel($type) }}</span>
                            @if ($type->requires_approval)
                                <span aria-hidden="true">&middot;</span>
                                <span>{{ __('messages.appointments_requires_confirmation') }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-guest-layout>
