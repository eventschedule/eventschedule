{{-- noindex: a ride board for one event, not content worth a search result. --}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :page-title="($event ? $event->translatedName() . ' - ' : '') . __('messages.carpool')" :no-index="true">

<x-slot name="head">
    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
</x-slot>

@php
    $isRtl = is_rtl();
    $accentColor = $role->accent_color ?? '#4E81FA';
    $subdomain = $role->subdomain;
    $eventHash = \App\Utils\UrlUtils::encodeId($event->id);
    // Event::guestUrlSlug(): the back link carries the id, and a slug such as "carpool" would lead
    // straight back to this page.
    $eventSlug = \App\Models\Event::guestUrlSlug($event->slug ?? 'event');
@endphp

{{-- The ride board on the stage (partials/guest-stage-styles): the event's name large at the
     top, then each offer as a card raised on the sheet and every form as a card to fill in. Every
     colour is a stage token or the accent the guest layout checked against the stage, light and
     dark; the states (approved, waiting, declined) are the kit's own. --}}
<style {!! nonce_attr() !!}>
    .gk-p-carpool { max-width: 52rem; display: flex; flex-direction: column; gap: 1.5rem; }
    .gk-p-carpool .gk-title { font-size: clamp(2.25rem, 1.5rem + 2.6vw, 3.5rem); }
    .gk-p-cp-head { display: flex; flex-direction: column; gap: .75rem; margin-bottom: .5rem; }
    .gk-p-back { display: inline-flex; align-items: center; gap: .375rem; align-self: flex-start; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-3); text-decoration: none; }
    .gk-p-back:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
    .gk-p-carpool.rtl .gk-p-back svg { transform: scaleX(-1); }
    .gk-p-cp-app { display: flex; flex-direction: column; gap: 1rem; }
    .gk-p-cp-pills { display: flex; gap: .5rem; overflow-x: auto; padding: .125rem; scrollbar-width: none; }
    .gk-p-cp-pills::-webkit-scrollbar { display: none; }
    .gk-p-carpool .gk-pill { background: transparent; }
    .gk-p-carpool .gk-pill-on { background: var(--es-accent); color: var(--es-accent-text); }
    .gk-p-carpool .gk-cta-main { flex: none; max-width: none; width: 100%; background: var(--es-accent); color: var(--es-accent-text); border: 1px solid var(--es-accent-edge, transparent); cursor: pointer; }
    .gk-p-cp-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.5rem; }
    .gk-p-cp-h2 { margin: 0; font-size: 1.5rem; font-weight: 750; letter-spacing: -.01em; color: var(--gk-ink); }
    .gk-p-cp-text { margin: 0; font-size: 1rem; line-height: 1.6; color: var(--gk-ink-2); }
    .gk-p-cp-form { display: grid; gap: 1rem; }
    .gk-p-cp-label { display: block; margin-bottom: .375rem; font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
    .gk-p-cp-req { color: var(--gk-bad); }
    .gk-p-carpool .gk-p-in { display: block; box-sizing: border-box; width: 100%; min-height: 3.125rem; padding: .625rem .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage); color: var(--gk-ink); font-size: 1rem; box-shadow: none; }
    .gk-p-carpool .gk-p-in::placeholder { color: var(--gk-ink-3); }
    .gk-p-carpool .gk-p-in:focus { border-color: var(--es-accent-readable); outline: none; box-shadow: 0 0 0 1px var(--es-accent-readable); }
    .gk-p-carpool .gk-p-in-sm { min-height: 2.75rem; padding: .375rem .75rem; font-size: .9375rem; }
    .gk-p-carpool .gk-p-in-auto { width: auto; }
    .gk-p-cp-acts { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
    .gk-p-cp-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .5rem; }
    .gk-p-cp-row > .gk-p-grow { flex: 1 1 12rem; min-width: 0; }
    .gk-p-cp-check { display: flex; align-items: flex-start; gap: .75rem; font-size: .9375rem; color: var(--gk-ink); cursor: pointer; }
    .gk-p-cp-check input { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .125rem; accent-color: var(--es-accent); }

    {{-- An offer. --}}
    .gk-p-offer-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
    .gk-p-offer-who { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem .625rem; }
    .gk-p-offer-name { font-size: 1.1875rem; font-weight: 700; color: var(--gk-ink); }
    .gk-p-offer-city { margin: .125rem 0 0; font-size: 1rem; color: var(--gk-ink-2); }
    .gk-p-rating { display: inline-flex; align-items: center; gap: .1875rem; font-size: .8125rem; color: var(--gk-ink-3); }
    .gk-p-rating svg { width: .875rem; height: .875rem; color: #f59e0b; }
    .gk-p-offer-facts { display: flex; flex-wrap: wrap; gap: .375rem 1.25rem; font-size: .9375rem; color: var(--gk-ink-2); }
    .gk-p-offer-facts > span { display: inline-flex; align-items: center; gap: .375rem; }
    .gk-p-offer-facts svg { flex: none; width: 1.0625rem; height: 1.0625rem; color: var(--gk-ink-3); }
    .gk-p-offer-note { margin: 0; font-size: .9375rem; font-style: italic; line-height: 1.55; color: var(--gk-ink-2); }
    .gk-p-offer-sec { display: flex; flex-direction: column; gap: .75rem; padding-top: 1rem; border-top: 1px solid var(--gk-line); }
    .gk-p-carpool .gk-label { margin: 0; }
    .gk-p-person { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem .75rem; padding: .875rem 1rem; border-radius: .875rem; background: var(--gk-stage-well); }
    .gk-p-person-name { font-size: .9375rem; font-weight: 650; color: var(--gk-ink); }
    .gk-p-person-sub { margin: .125rem 0 0; font-size: .8125rem; color: var(--gk-ink-2); }
    .gk-p-person-acts { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .gk-p-ok { color: var(--gk-ok); font-weight: 650; }
    .gk-p-wait { color: var(--gk-warn); font-weight: 650; }
    .gk-p-no { color: var(--gk-bad); font-weight: 650; }
    .gk-p-status { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; font-size: .9375rem; }
    .gk-p-carpool .gk-btn-danger { border-color: color-mix(in srgb, var(--gk-bad) 45%, transparent); background: transparent; color: var(--gk-bad); }
    .gk-p-carpool .gk-btn-danger:hover { background: var(--gk-bad-bg); }
    .gk-p-quiet-btn { padding: 0; border: 0; background: none; color: var(--gk-ink-3); font: inherit; font-size: .8125rem; cursor: pointer; }
    .gk-p-quiet-btn:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
    .gk-p-cp-empty { display: flex; flex-direction: column; align-items: center; gap: .75rem; padding: 3rem 1rem; text-align: center; color: var(--gk-ink-2); }
    .gk-p-cp-empty svg { width: 3rem; height: 3rem; color: var(--gk-ink-3); }
    .gk-p-carpool .gk-note p { margin: 0; }
    .gk-p-carpool a:focus-visible, .gk-p-carpool button:focus-visible { outline: 2px solid var(--es-accent-readable); outline-offset: 2px; }
</style>

<div class="gk-stage gk-p-carpool {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

        {{-- The event --}}
        <header class="gk-p-cp-head">
            <a href="{{ config('app.hosted')
                ? ($date
                    ? route('event.view_guest_full', ['subdomain' => $subdomain, 'slug' => $eventSlug, 'id' => $eventHash, 'date' => $date])
                    : route('event.view_guest_with_id', ['subdomain' => $subdomain, 'slug' => $eventSlug, 'id' => $eventHash]))
                : '/' . $subdomain . '/' . $eventSlug . '/' . $eventHash . ($date ? '/' . $date : '') }}"
               class="gk-p-back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                {{ __('messages.carpool_back_to_event') }}
            </a>
            @php
                $carpoolWhen = null;
                if ($event->is_multi_day) {
                    $carpoolWhen = $event->getDateRangeDisplay($date);
                } elseif ($date) {
                    $startDt = $event->getStartDateTime($date, true);
                    $carpoolWhen = $startDt ? $startDt->translatedFormat('F j, Y') : null;
                } elseif ($event->starts_at) {
                    $carpoolWhen = $event->getStartDateTime(null, true)->translatedFormat('F j, Y');
                }
            @endphp
            @if ($carpoolWhen)
            <p class="gk-dateline">{{ $carpoolWhen }}</p>
            @endif
            <h1 class="gk-title">{{ __('messages.carpool') }}</h1>
            <p class="gk-subtitle" dir="auto">{{ $event->name }}</p>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
        <div class="gk-note gk-note-ok" role="status"><div class="gk-note-body">{{ session('success') }}</div></div>
        @endif
        @if (session('error'))
        <div class="gk-note gk-note-bad" role="alert"><div class="gk-note-body">{{ session('error') }}</div></div>
        @endif
        @if ($errors->any())
        <div class="gk-note gk-note-bad" role="alert">
            <div class="gk-note-body">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Not logged in --}}
        @if (! $user)
        <div class="gk-card gk-band gk-p-cp-empty">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <p class="gk-p-cp-text">{{ __('messages.carpool_login_required') }}</p>
            <a href="{{ app_url(route('login', [], false)) }}" class="gk-btn gk-btn-primary gk-btn-lg">
                {{ __('messages.log_in') }}
            </a>
        </div>

        {{-- Disclaimer gate --}}
        @elseif (! $user->hasAgreedToCarpool())
        <div class="gk-card gk-band gk-p-cp-card">
            <h2 class="gk-p-cp-h2">{{ __('messages.carpool_disclaimer_title') }}</h2>
            <p class="gk-p-cp-text">{{ __('messages.carpool_disclaimer_text') }}</p>

            <form method="POST" action="{{ route('carpool.agree_disclaimer', ['subdomain' => $subdomain, 'event_hash' => $eventHash]) }}" class="gk-p-cp-form">
                @csrf
                <label class="gk-p-cp-check">
                    <input type="checkbox" name="agree" value="1" required>
                    <span>{{ __('messages.carpool_disclaimer_checkbox', ['app' => config('app.name')]) }}</span>
                </label>
                <div class="gk-p-cp-acts">
                    <button type="submit" class="gk-btn gk-btn-primary gk-btn-lg">
                        {{ __('messages.confirm') }}
                    </button>
                </div>
            </form>
        </div>

        @else
        {{-- Main carpool content --}}
        <div id="carpool-app" data-vue-root class="gk-p-cp-app">

            {{-- Direction filter tabs --}}
            <div class="gk-p-cp-pills">
                <button type="button" class="carpool-filter-tab gk-pill" :class="filter === 'all' ? 'gk-pill-on' : ''" :aria-pressed="filter === 'all' ? 'true' : 'false'" @click="filter = 'all'">
                    {{ __('messages.all') }}
                </button>
                <button type="button" class="carpool-filter-tab gk-pill" :class="filter === 'to_event' ? 'gk-pill-on' : ''" :aria-pressed="filter === 'to_event' ? 'true' : 'false'" @click="filter = 'to_event'">
                    {{ __('messages.carpool_to_event') }}
                </button>
                <button type="button" class="carpool-filter-tab gk-pill" :class="filter === 'from_event' ? 'gk-pill-on' : ''" :aria-pressed="filter === 'from_event' ? 'true' : 'false'" @click="filter = 'from_event'">
                    {{ __('messages.carpool_from_event') }}
                </button>
                <button type="button" class="carpool-filter-tab gk-pill" :class="filter === 'round_trip' ? 'gk-pill-on' : ''" :aria-pressed="filter === 'round_trip' ? 'true' : 'false'" @click="filter = 'round_trip'">
                    {{ __('messages.carpool_round_trip') }}
                </button>
            </div>

            {{-- City filter --}}
            @if ($offers->count() > 5)
            <div>
                <label for="carpool-city-filter" class="sr-only">{{ __('messages.carpool_filter_by_city') }}</label>
                <input id="carpool-city-filter" type="text" v-model="cityFilter" placeholder="{{ __('messages.carpool_filter_by_city') }}" class="gk-p-in">
            </div>
            @endif

            {{-- Offer a ride button --}}
            @if (! $eventEnded)
            <button type="button" @click="showOfferForm = !showOfferForm" :aria-expanded="showOfferForm ? 'true' : 'false'" class="gk-cta-main">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('messages.carpool_offer_ride') }}
            </button>

            {{-- Offer form --}}
            <div v-show="showOfferForm" style="display:none" class="gk-card gk-band gk-p-cp-card">
                <h2 class="gk-p-cp-h2">{{ __('messages.carpool_offer_ride') }}</h2>
                <form method="POST" action="{{ route('carpool.store_offer', ['subdomain' => $subdomain, 'event_hash' => $eventHash]) }}" class="gk-p-cp-form">
                    @csrf
                    @if ($isRecurring && $date)
                    <input type="hidden" name="event_date" value="{{ $date }}">
                    @endif

                    <div>
                        <label for="carpool-direction" class="gk-p-cp-label">{{ __('messages.carpool_direction') }} <span class="gk-p-cp-req" aria-hidden="true">*</span></label>
                        <select id="carpool-direction" name="direction" required class="gk-p-in">
                            <option value="to_event" {{ old('direction') === 'to_event' ? 'selected' : '' }}>{{ __('messages.carpool_to_event') }}</option>
                            <option value="from_event" {{ old('direction') === 'from_event' ? 'selected' : '' }}>{{ __('messages.carpool_from_event') }}</option>
                            <option value="round_trip" {{ old('direction') === 'round_trip' ? 'selected' : '' }}>{{ __('messages.carpool_round_trip') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="carpool-city" class="gk-p-cp-label">{{ __('messages.carpool_city') }} <span class="gk-p-cp-req" aria-hidden="true">*</span></label>
                        <input id="carpool-city" type="text" name="city" value="{{ old('city') }}" required maxlength="255" class="gk-p-in" placeholder="{{ __('messages.carpool_city_placeholder') }}">
                    </div>

                    <div>
                        <label for="carpool-departure" class="gk-p-cp-label">{{ __('messages.carpool_departure_time') }}</label>
                        <input id="carpool-departure" type="time" name="departure_time" value="{{ old('departure_time') }}" class="gk-p-in">
                    </div>

                    <div>
                        <label for="carpool-meeting" class="gk-p-cp-label">{{ __('messages.carpool_meeting_point') }}</label>
                        <input id="carpool-meeting" type="text" name="meeting_point" value="{{ old('meeting_point') }}" maxlength="255" class="gk-p-in" placeholder="{{ __('messages.carpool_meeting_point_placeholder') }}">
                    </div>

                    <div>
                        <label for="carpool-spots" class="gk-p-cp-label">{{ __('messages.carpool_spots') }} <span class="gk-p-cp-req" aria-hidden="true">*</span></label>
                        <select id="carpool-spots" name="total_spots" required class="gk-p-in">
                            @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ (int) old('total_spots', 1) === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="carpool-note" class="gk-p-cp-label">{{ __('messages.carpool_note') }}</label>
                        <textarea id="carpool-note" name="note" maxlength="500" rows="2" class="gk-p-in" placeholder="{{ __('messages.carpool_note_placeholder') }}" v-pre>{{ old('note') }}</textarea>
                    </div>

                    <div class="gk-p-cp-acts">
                        <button type="button" @click="showOfferForm = false" class="gk-btn gk-btn-secondary gk-btn-lg">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="gk-btn gk-btn-primary gk-btn-lg">
                            {{ __('messages.carpool_create_offer') }}
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Offers list --}}
            @forelse ($offers as $offer)
            @php
                $offerExpired = false;
                if (in_array($offer->direction, ['to_event', 'round_trip'])) {
                    if ($event->is_multi_day) {
                        $offerExpired = $eventEnded;
                    } else {
                        // Venue-local occurrence start, matching CarpoolController's gates.
                        // Without the timezone this is a day early and expires the offer early.
                        $st = $event->getStartDateTime($date, true, $event->scheduleTimezone());
                        $offerExpired = $st && $st->isPast();
                    }
                } else {
                    $offerExpired = $eventEnded;
                }
            @endphp
            <div class="gk-card gk-p-cp-card carpool-offer" data-direction="{{ $offer->direction }}" data-city="{{ strtolower($offer->city) }}" v-show="(filter === 'all' || filter === '{{ $offer->direction }}') && (cityFilter === '' || {{ json_encode(strtolower($offer->city)) }}.includes(cityFilter.toLowerCase()))">

                <div class="gk-p-offer-top">
                    <div style="min-width: 0">
                        <div class="gk-p-offer-who">
                            <span v-pre class="gk-p-offer-name" dir="auto">{{ $offer->user->name }}</span>
                            @php
                                $driverRating = $carpoolRatings[$offer->user_id]['rating'] ?? null;
                                $driverReviewCount = $carpoolRatings[$offer->user_id]['count'] ?? 0;
                            @endphp
                            @if ($driverReviewCount > 0)
                            <span class="gk-p-rating">
                                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                                {{ number_format($driverRating, 1) }} ({{ $driverReviewCount }})
                            </span>
                            @endif
                        </div>
                        <p v-pre class="gk-p-offer-city" dir="auto">{{ $offer->city }}</p>
                    </div>
                    <span class="gk-chip {{ $offer->direction === 'to_event' ? 'gk-chip-accent' : '' }}">
                        {{ $offer->directionLabel() }}
                    </span>
                </div>

                <div class="gk-p-offer-facts">
                    @if ($offer->departure_time)
                    <span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <bdi dir="ltr">{{ \Carbon\Carbon::parse($offer->departure_time)->format('H:i') }}</bdi>
                    </span>
                    @endif
                    @if ($offer->meeting_point)
                    <span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span v-pre dir="auto">{{ $offer->meeting_point }}</span>
                    </span>
                    @endif
                    <span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        @php $available = $offer->availableSpots(); @endphp
                        {{ trans_choice('messages.carpool_spots_available', $available, ['count' => $available, 'total' => $offer->total_spots]) }}
                    </span>
                </div>

                @if ($offer->note)
                <p v-pre class="gk-p-offer-note" dir="auto">{{ $offer->note }}</p>
                @endif

                {{-- Contact info for approved riders --}}
                @if ($user && $myRequests->has($offer->id) && $myRequests[$offer->id]->status === 'approved')
                <div class="gk-note gk-note-ok">
                    <div class="gk-note-body">
                        <p style="font-weight: 650">{{ __('messages.carpool_contact_info') }}</p>
                        <p v-pre>{{ __('messages.email') }}: {{ $offer->user->email }}</p>
                        @if ($offer->user->phone)
                        <p v-pre>{{ __('messages.phone') }}: {{ $offer->user->phone }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Driver's own offer: show pending requests --}}
                @if ($user && $offer->user_id === $user->id)
                <div class="gk-p-offer-sec">
                    <p class="gk-label">{{ __('messages.carpool_your_offer') }}</p>

                    @foreach ($offer->pendingRequests as $pendingReq)
                    <div class="gk-p-person">
                        <div style="min-width: 0">
                            <span v-pre class="gk-p-person-name" dir="auto">{{ $pendingReq->user->name }}</span>
                            @php
                                $riderRating = $carpoolRatings[$pendingReq->user_id]['rating'] ?? null;
                                $riderReviewCount = $carpoolRatings[$pendingReq->user_id]['count'] ?? 0;
                            @endphp
                            @if ($riderReviewCount > 0)
                            <span class="gk-p-rating" style="margin-inline-start: .375rem">
                                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                                {{ number_format($riderRating, 1) }}
                            </span>
                            @endif
                            @if ($pendingReq->message)
                            <p v-pre class="gk-p-person-sub" style="font-style: italic" dir="auto">{{ $pendingReq->message }}</p>
                            @endif
                        </div>
                        @if (! $eventEnded)
                        <div class="gk-p-person-acts">
                            <form method="POST" action="{{ route('carpool.decline', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'request_hash' => \App\Utils\UrlUtils::encodeId($pendingReq->id)]) }}">
                                @csrf
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-danger">{{ __('messages.decline') }}</button>
                            </form>
                            <form method="POST" action="{{ route('carpool.approve', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'request_hash' => \App\Utils\UrlUtils::encodeId($pendingReq->id)]) }}">
                                @csrf
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-primary">{{ __('messages.approve') }}</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    @foreach ($offer->approvedRequests as $approvedReq)
                    <div class="gk-p-person" style="flex-direction: column; align-items: stretch">
                        <div class="gk-p-status">
                            <div style="min-width: 0">
                                <span v-pre class="gk-p-person-name" dir="auto">{{ $approvedReq->user->name }}</span>
                                <p v-pre class="gk-p-person-sub">{{ $approvedReq->user->email }}@if($approvedReq->user->phone) &middot; {{ $approvedReq->user->phone }}@endif</p>
                            </div>
                            <div class="gk-p-person-acts">
                                <span class="gk-p-ok" style="font-size: .875rem">{{ __('messages.approved') }}</span>
                                @php $riderReportKey = \App\Utils\UrlUtils::encodeId($offer->id) . '_' . \App\Utils\UrlUtils::encodeId($approvedReq->user_id); @endphp
                                <button type="button" @click="reportRiderKey = reportRiderKey === '{{ $riderReportKey }}' ? null : '{{ $riderReportKey }}'" class="gk-p-quiet-btn">{{ __('messages.report') }}</button>
                            </div>
                        </div>
                        <div v-show="reportRiderKey === '{{ $riderReportKey }}'" style="display: none">
                            <form method="POST" action="{{ route('carpool.report', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'user_hash' => \App\Utils\UrlUtils::encodeId($approvedReq->user_id)]) }}" class="gk-p-cp-row">
                                @csrf
                                <input type="text" name="reason" required maxlength="1000" class="gk-p-in gk-p-in-sm gk-p-grow" placeholder="{{ __('messages.carpool_report_reason') }}" aria-label="{{ __('messages.carpool_report_reason') }}">
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-danger">{{ __('messages.report') }}</button>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    @if (! $eventEnded)
                    <div class="gk-p-cp-acts" style="gap: .5rem">
                        <button type="button" @click="editSpotsOfferId = editSpotsOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}' ? null : '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="gk-btn gk-btn-sm gk-btn-secondary">{{ __('messages.carpool_edit_spots') }}</button>
                        <form method="POST" action="{{ route('carpool.cancel_offer', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}">
                            @csrf
                            <button type="submit" class="gk-btn gk-btn-sm gk-btn-danger" data-confirm="{{ __('messages.carpool_cancel_offer_confirm') }}">{{ __('messages.carpool_cancel_offer') }}</button>
                        </form>
                    </div>

                    <div v-show="editSpotsOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" style="display: none">
                        <form method="POST" action="{{ route('carpool.update_spots', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="gk-p-cp-row">
                            @csrf
                            <div>
                                <label for="carpool-spots-{{ $offer->id }}" class="gk-p-cp-label">{{ __('messages.carpool_spots') }}</label>
                                <select id="carpool-spots-{{ $offer->id }}" name="total_spots" class="gk-p-in gk-p-in-sm gk-p-in-auto">
                                    @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $offer->total_spots === $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="button" @click="editSpotsOfferId = null" class="gk-btn gk-btn-sm gk-btn-secondary">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="gk-btn gk-btn-sm gk-btn-primary">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Other user's offer: request/status --}}
                @elseif ($user)
                <div class="gk-p-offer-sec">
                    @if ($myRequests->has($offer->id))
                        @php $myRequest = $myRequests[$offer->id]; @endphp
                        @if ($myRequest->status === 'pending')
                        <div class="gk-p-status">
                            <span class="gk-p-wait">{{ __('messages.carpool_request_pending') }}</span>
                            @if (! $eventEnded)
                            <form method="POST" action="{{ route('carpool.cancel_request', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'request_hash' => \App\Utils\UrlUtils::encodeId($myRequest->id)]) }}">
                                @csrf
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-secondary">{{ __('messages.cancel') }}</button>
                            </form>
                            @endif
                        </div>
                        @elseif ($myRequest->status === 'approved')
                        <div class="gk-p-status">
                            <span class="gk-p-ok">{{ __('messages.carpool_request_approved_status') }}</span>
                            @if (! $eventEnded)
                            <form method="POST" action="{{ route('carpool.cancel_request', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'request_hash' => \App\Utils\UrlUtils::encodeId($myRequest->id)]) }}">
                                @csrf
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-secondary" data-confirm="{{ __('messages.carpool_cancel_request_confirm') }}">{{ __('messages.carpool_withdraw') }}</button>
                            </form>
                            @endif
                        </div>
                        @elseif ($myRequest->status === 'declined')
                        <div class="gk-p-cp-form" style="gap: .75rem">
                            <span class="gk-p-no" style="font-size: .9375rem">{{ __('messages.carpool_request_declined_status') }}</span>
                            @if (! $offerExpired && ! $offer->isFull())
                            <form method="POST" action="{{ route('carpool.request_ride', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="gk-p-cp-row">
                                @csrf
                                <input type="text" name="message" maxlength="500" class="gk-p-in gk-p-grow" placeholder="{{ __('messages.carpool_message_placeholder') }}" aria-label="{{ __('messages.carpool_message_placeholder') }}">
                                <button type="submit" class="gk-btn gk-btn-primary" style="min-height: 3.125rem">
                                    {{ __('messages.carpool_request_spot') }}
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                    @elseif (! $offerExpired && ! $offer->isFull())
                    {{-- Request form --}}
                    <form method="POST" action="{{ route('carpool.request_ride', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="gk-p-cp-row">
                        @csrf
                        <input type="text" name="message" maxlength="500" class="gk-p-in gk-p-grow" placeholder="{{ __('messages.carpool_message_placeholder') }}" aria-label="{{ __('messages.carpool_message_placeholder') }}">
                        <button type="submit" class="gk-btn gk-btn-primary" style="min-height: 3.125rem">
                            {{ __('messages.carpool_request_spot') }}
                        </button>
                    </form>
                    @elseif ($offer->isFull())
                    <span class="gk-p-cp-text" style="font-size: .9375rem">{{ __('messages.carpool_offer_full') }}</span>
                    @endif

                    {{-- Report link --}}
                    @if ($offer->user_id !== $user->id && $offer->approvedRequests->contains('user_id', $user->id))
                    <div>
                        <button type="button" @click="reportOfferId = reportOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}' ? null : '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="gk-p-quiet-btn">{{ __('messages.report') }}</button>
                        <div v-show="reportOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" style="display: none; margin-top: .5rem">
                            <form method="POST" action="{{ route('carpool.report', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'user_hash' => \App\Utils\UrlUtils::encodeId($offer->user_id)]) }}" class="gk-p-cp-row">
                                @csrf
                                <input type="text" name="reason" required maxlength="1000" class="gk-p-in gk-p-in-sm gk-p-grow" placeholder="{{ __('messages.carpool_report_reason') }}" aria-label="{{ __('messages.carpool_report_reason') }}">
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-danger">{{ __('messages.report') }}</button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Review section (after event ends) --}}
                @if ($eventEnded && $user)
                    @php
                        $isDriver = $offer->user_id === $user->id;
                        $isApprovedRider = $offer->approvedRequests->contains('user_id', $user->id);
                        $reviewableUsers = collect();
                        if ($isDriver) {
                            foreach ($offer->approvedRequests as $ar) {
                                if (! $myReviews->where('carpool_offer_id', $offer->id)->where('reviewed_user_id', $ar->user_id)->first()) {
                                    $reviewableUsers->push($ar->user);
                                }
                            }
                        } elseif ($isApprovedRider) {
                            if (! $myReviews->where('carpool_offer_id', $offer->id)->where('reviewed_user_id', $offer->user_id)->first()) {
                                $reviewableUsers->push($offer->user);
                            }
                        }
                    @endphp
                    @if ($reviewableUsers->count() > 0)
                    <div class="gk-p-offer-sec">
                        <p class="gk-label">{{ __('messages.carpool_leave_review') }}</p>
                        @foreach ($reviewableUsers as $reviewUser)
                        <form method="POST" action="{{ route('carpool.store_review', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}">
                            @csrf
                            <input type="hidden" name="reviewed_user_id" value="{{ \App\Utils\UrlUtils::encodeId($reviewUser->id) }}">
                            <p class="gk-p-cp-label" v-pre>{{ __('messages.carpool_review_for', ['name' => $reviewUser->name]) }}</p>
                            <div class="gk-p-cp-row">
                                <select name="rating" required class="gk-p-in gk-p-in-sm gk-p-in-auto" aria-label="{{ __('messages.carpool_leave_review') }}">
                                    @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} {{ str_repeat('★', $i) }}</option>
                                    @endfor
                                </select>
                                <input type="text" name="comment" maxlength="1000" class="gk-p-in gk-p-in-sm gk-p-grow" placeholder="{{ __('messages.carpool_review_comment') }}" aria-label="{{ __('messages.carpool_review_comment') }}">
                                <button type="submit" class="gk-btn gk-btn-sm gk-btn-primary">{{ __('messages.submit') }}</button>
                            </div>
                        </form>
                        @endforeach
                    </div>
                    @endif
                @endif

            </div>
            @empty
            <div class="gk-p-cp-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <p class="gk-p-cp-text">{{ __('messages.carpool_no_offers') }}</p>
            </div>
            @endforelse

            {{-- Phone nudge --}}
            @if ($user && ! $user->phone)
            <div class="gk-note">
                <svg class="gk-note-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="gk-note-body">
                    {{ __('messages.carpool_add_phone') }}
                    <x-link href="{{ app_url(route('profile.edit', [], false)) }}">{{ __('messages.carpool_update_profile') }}</x-link>
                </p>
            </div>
            @endif

        </div>
        @endif

</div>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Vue !== 'undefined' && document.getElementById('carpool-app')) {
        try {
            Vue.createApp({
                data() {
                    return {
                        filter: 'all',
                        cityFilter: '',
                        showOfferForm: {{ $errors->has('direction') || $errors->has('city') || $errors->has('total_spots') ? 'true' : 'false' }},
                        reportOfferId: null,
                        reportRiderKey: null,
                        editSpotsOfferId: null,
                    };
                }
            }).mount('#carpool-app[data-vue-root]');
        } catch (e) {
            console.error('Carpool Vue mount error:', e);
        }
    }
});
</script>

</x-app-guest-layout>
