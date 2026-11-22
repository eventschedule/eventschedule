<x-app-guest-layout :role="$role" :page-title="__('messages.appointments')">
    @php
        $locationLabel = fn ($type) => match ($type->location_type) {
            'online' => __('messages.online'),
            'phone' => __('messages.appointments_phone_call'),
            default => __('messages.appointments_in_person'),
        };
    @endphp

    {{-- The page's own pieces on the stage (partials/guest-stage-styles): the schedule beside its
         name at the top, and each appointment type as a card raised on the sheet. The colours are
         the stage's own tokens, which GuestTheme checks against the schedule's accent in both
         modes, so nothing here mixes a colour of its own. --}}
    <style {!! nonce_attr() !!}>
        .gk-p-appt { max-width: 64rem; }
        .gk-p-appt-head { display: flex; flex-direction: column; gap: .875rem; margin-bottom: 2.5rem; }
        .gk-p-appt .gk-title { font-size: clamp(2.25rem, 1.5rem + 2.8vw, 3.75rem); }
        .gk-p-who { display: inline-flex; align-items: center; gap: .625rem; align-self: flex-start; max-width: 100%; color: var(--gk-ink-2); font-size: .9375rem; font-weight: 600; text-decoration: none; }
        .gk-p-who:hover { color: var(--gk-ink); }
        .gk-p-who:hover .gk-p-who-name { text-decoration: underline; text-underline-offset: 3px; }
        .gk-p-who-img { flex: none; width: 2.25rem; height: 2.25rem; border-radius: .625rem; object-fit: cover; background: var(--gk-stage-raised); }
        .gk-p-who-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gk-p-who-arrow { flex: none; }
        [dir="rtl"] .gk-p-who-arrow { transform: scaleX(-1); }
        .gk-p-group + .gk-p-group { margin-top: 2.5rem; }
        .gk-p-group-name { margin: 0 0 1rem; font-size: 1.25rem; font-weight: 700; color: var(--gk-ink); }
        .gk-p-types { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 22rem), 1fr)); }
        .gk-p-type { display: flex; flex-direction: column; gap: .5rem; padding: 1.5rem; text-decoration: none; transition: background-color var(--gk-swap), border-color var(--gk-swap); }
        .gk-p-type:hover { background: var(--gk-stage-well); }
        .gk-p-type:focus-visible { outline: 2px solid var(--es-accent-readable); outline-offset: 2px; }
        .gk-p-type-name { margin: 0; font-size: 1.375rem; font-weight: 750; line-height: 1.2; letter-spacing: -.01em; color: var(--gk-ink); overflow-wrap: anywhere; }
        .gk-p-type-host { margin: 0; font-size: .9375rem; color: var(--gk-ink-2); }
        .gk-p-type-desc { margin: 0; font-size: 1rem; line-height: 1.55; color: var(--gk-ink-2); }
        .gk-p-type-foot { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem .875rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--gk-line); font-size: .9375rem; color: var(--gk-ink-2); }
        .gk-p-type-go { display: inline-flex; align-items: center; gap: .375rem; margin-inline-start: auto; font-weight: 650; color: var(--es-accent-readable); }
        [dir="rtl"] .gk-p-type-go svg { transform: scaleX(-1); }
        .gk-p-type-head { display: flex; flex-direction: column; gap: .25rem; }
        .gk-p-type-fact { display: inline-flex; gap: .875rem; }
        {{-- An odd last card takes the whole row, so two-up never leaves an empty cell. --}}
        .gk-p-types > .gk-p-type:last-child:nth-child(odd) { grid-column: 1 / -1; }
    </style>

    {{-- Wider, with the types two-up: showBook() redirects when there is only one type, so this page
         always has at least two cards and a single column left half the width empty. --}}
    <div id="appt-chooser" class="gk-stage gk-p-appt">
        <header class="gk-p-appt-head">
            <a href="{{ $role->getGuestUrl() }}" class="gk-p-who">
                @if ($role->profile_image_url)
                    <img src="{{ $role->profile_image_url }}" alt="" class="gk-p-who-img" width="36" height="36">
                @endif
                <span class="gk-p-who-name" dir="auto">{{ $role->name }}</span>
                <svg class="gk-p-who-arrow w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>
            <h1 class="gk-title">{{ $role->customLabel('book_a_time') }}</h1>
            <p class="gk-subtitle" dir="auto">{{ __('messages.appointments_with', ['schedule' => $role->name]) }}</p>
        </header>

        @php
            // With two or more team members hosting types here, the cards are grouped under each
            // one's name ("with Sam"), and the schedule's own types under the schedule's name.
            // With one, or none, it is one list and a hosted card says who it is with.
            $byHost = $types->groupBy(fn ($type) => (int) $type->host_user_id);
            $grouped = $byHost->keys()->filter()->count() >= 2;
            $groups = $grouped ? $byHost->sortKeys() : collect([0 => $types]);
            $cardHeading = $grouped ? 'h3' : 'h2';
        @endphp

        @foreach ($groups as $groupHostId => $groupTypes)
        <section class="gk-p-group">
            @if ($grouped)
                <h2 class="gk-p-group-name" dir="auto">{{ $groupHostId ? __('messages.appointments_with_host', ['name' => $groupTypes->first()->hostName() ?? '']) : $role->name }}</h2>
            @endif
            <div class="gk-p-types">
                @foreach ($groupTypes as $type)
                    @php
                        $priceLabel = $type->isFree()
                            ? __('messages.free')
                            : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code);
                        $typeHost = $type->hostName();
                        // Self-describing out of context: a screen reader reading the link list alone
                        // would otherwise only hear the type name.
                        $ariaLabel = trim($type->name.($typeHost ? ' - '.__('messages.appointments_with_host', ['name' => $typeHost]) : '').' - '.$type->duration_minutes.' '.__('messages.minutes').' - '.$priceLabel);
                    @endphp
                    <a href="{{ route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]) }}"
                       aria-label="{{ $ariaLabel }}"
                       class="es-type-card gk-card gk-p-type">
                        <div class="gk-p-type-head">
                            <{{ $cardHeading }} class="gk-p-type-name" dir="auto">{{ $type->name }}</{{ $cardHeading }}>
                            @if ($typeHost && ! $grouped)
                                <p class="gk-p-type-host" dir="auto">{{ __('messages.appointments_with_host', ['name' => $typeHost]) }}</p>
                            @endif
                        </div>
                        @if ($type->description)
                            <p class="gk-p-type-desc" dir="auto">{{ $type->description }}</p>
                        @endif
                        <div class="gk-p-type-foot">
                            <span class="gk-chip gk-chip-accent">{{ $type->duration_minutes }} {{ __('messages.minutes') }}</span>
                            <span>{{ $priceLabel }}</span>
                            {{-- Each dot travels with the fact after it, so a line that wraps never ends on one. --}}
                            <span class="gk-p-type-fact"><span aria-hidden="true">&middot;</span><span>{{ $locationLabel($type) }}</span></span>
                            @if ($type->requires_approval)
                                <span class="gk-p-type-fact"><span aria-hidden="true">&middot;</span><span>{{ __('messages.appointments_requires_confirmation') }}</span></span>
                            @endif
                            <span class="gk-p-type-go" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endforeach
    </div>
</x-app-guest-layout>
