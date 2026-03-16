<x-app-guest-layout :role="$role" :fonts="$fonts">

<x-slot name="head">
    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
</x-slot>

@php
    $isRtl = is_rtl();
    $accentColor = $role->accent_color ?? '#4E81FA';
    $subdomain = $role->subdomain;
    $eventHash = \App\Utils\UrlUtils::encodeId($event->id);
@endphp

<div class="container mx-auto max-w-2xl px-0 sm:px-5 pt-4 pb-20 sm:pb-8" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

        {{-- Event info card --}}
        <div class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-6 mb-6">
            <a href="{{ config('app.hosted')
                ? ($date
                    ? route('event.view_guest_full', ['subdomain' => $subdomain, 'slug' => $event->slug ?? 'event', 'id' => $eventHash, 'date' => $date])
                    : route('event.view_guest_with_id', ['subdomain' => $subdomain, 'slug' => $event->slug ?? 'event', 'id' => $eventHash]))
                : '/' . $subdomain . '/' . ($event->slug ?? 'event') . '/' . $eventHash . ($date ? '/' . $date : '') }}"
               class="text-sm text-gray-500 dark:text-[#9ca3af] hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1 mb-3">
                {{ $isRtl ? '→' : '←' }} {{ __('messages.carpool_back_to_event') }}
            </a>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">{{ __('messages.carpool') }}</h1>
            <p class="text-lg text-gray-700 dark:text-[#d1d5db]">{{ $event->name }}</p>
            @if ($date)
                @php $startDt = $event->getStartDateTime($date, true); @endphp
                @if ($startDt)
                <p class="text-sm text-gray-500 dark:text-[#9ca3af] mt-1">
                    {{ $startDt->translatedFormat('F j, Y') }}
                </p>
                @endif
            @elseif ($event->starts_at)
            <p class="text-sm text-gray-500 dark:text-[#9ca3af] mt-1">
                {{ $event->getStartDateTime(null, true)->translatedFormat('F j, Y') }}
            </p>
            @endif
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm">
            {{ session('error') }}
        </div>
        @endif
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Not logged in --}}
        @if (! $user)
        <div class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-6 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <p class="text-gray-600 dark:text-[#d1d5db] mb-4">{{ __('messages.carpool_login_required') }}</p>
            <a href="{{ app_url(route('login', [], false)) }}" class="inline-block px-4 py-2 rounded-lg text-white font-medium text-sm" style="background-color: {{ $accentColor }};">
                {{ __('messages.log_in') }}
            </a>
        </div>

        {{-- Disclaimer gate --}}
        @elseif (! $user->hasAgreedToCarpool())
        <div class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('messages.carpool_disclaimer_title') }}</h2>
            <p class="text-sm text-gray-600 dark:text-[#d1d5db] mb-4">{{ __('messages.carpool_disclaimer_text') }}</p>

            <form method="POST" action="{{ route('carpool.agree_disclaimer', ['subdomain' => $subdomain, 'event_hash' => $eventHash]) }}">
                @csrf
                <label class="flex items-start gap-3 mb-4 cursor-pointer">
                    <input type="checkbox" name="agree" value="1" class="mt-1 rounded border-gray-300 dark:border-gray-500 dark:bg-[#1e1e1e] text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" required>
                    <span class="text-sm text-gray-700 dark:text-[#d1d5db]">{{ __('messages.carpool_disclaimer_checkbox', ['app' => config('app.name')]) }}</span>
                </label>
                <button type="submit" class="px-4 py-3 text-base rounded-lg text-white font-medium" style="background-color: {{ $accentColor }};">
                    {{ __('messages.confirm') }}
                </button>
            </form>
        </div>

        @else
        {{-- Main carpool content --}}
        <div id="carpool-app">

            {{-- Direction filter tabs --}}
            <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
                <button type="button" class="carpool-filter-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors" :class="filter === 'all' ? 'text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-[#d1d5db]'" :style="filter === 'all' ? 'background-color: {{ $accentColor }}' : ''" @click="filter = 'all'">
                    {{ __('messages.all') }}
                </button>
                <button type="button" class="carpool-filter-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors" :class="filter === 'to_event' ? 'text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-[#d1d5db]'" :style="filter === 'to_event' ? 'background-color: {{ $accentColor }}' : ''" @click="filter = 'to_event'">
                    {{ __('messages.carpool_to_event') }}
                </button>
                <button type="button" class="carpool-filter-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors" :class="filter === 'from_event' ? 'text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-[#d1d5db]'" :style="filter === 'from_event' ? 'background-color: {{ $accentColor }}' : ''" @click="filter = 'from_event'">
                    {{ __('messages.carpool_from_event') }}
                </button>
                <button type="button" class="carpool-filter-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors" :class="filter === 'round_trip' ? 'text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-[#d1d5db]'" :style="filter === 'round_trip' ? 'background-color: {{ $accentColor }}' : ''" @click="filter = 'round_trip'">
                    {{ __('messages.carpool_round_trip') }}
                </button>
            </div>

            {{-- City filter --}}
            @if ($offers->count() > 5)
            <div class="mb-4">
                <input type="text" v-model="cityFilter" placeholder="{{ __('messages.carpool_filter_by_city') }}" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
            </div>
            @endif

            {{-- Offer a ride button --}}
            @if (! $eventEnded)
            <div class="mb-4">
                <button type="button" @click="showOfferForm = !showOfferForm" class="w-full px-4 py-3 text-base rounded-lg text-white font-medium flex items-center justify-center gap-2" style="background-color: {{ $accentColor }};">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('messages.carpool_offer_ride') }}
                </button>
            </div>

            {{-- Offer form --}}
            <div v-show="showOfferForm" style="display:none" class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.carpool_offer_ride') }}</h2>
                <form method="POST" action="{{ route('carpool.store_offer', ['subdomain' => $subdomain, 'event_hash' => $eventHash]) }}">
                    @csrf
                    @if ($isRecurring && $date)
                    <input type="hidden" name="event_date" value="{{ $date }}">
                    @endif

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_direction') }} <span class="text-red-500">*</span></label>
                        <select name="direction" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                            <option value="to_event" {{ old('direction') === 'to_event' ? 'selected' : '' }}>{{ __('messages.carpool_to_event') }}</option>
                            <option value="from_event" {{ old('direction') === 'from_event' ? 'selected' : '' }}>{{ __('messages.carpool_from_event') }}</option>
                            <option value="round_trip" {{ old('direction') === 'round_trip' ? 'selected' : '' }}>{{ __('messages.carpool_round_trip') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_city') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="city" value="{{ old('city') }}" required maxlength="255" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_city_placeholder') }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_departure_time') }}</label>
                        <input type="time" name="departure_time" value="{{ old('departure_time') }}" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_meeting_point') }}</label>
                        <input type="text" name="meeting_point" value="{{ old('meeting_point') }}" maxlength="255" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_meeting_point_placeholder') }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_spots') }} <span class="text-red-500">*</span></label>
                        <select name="total_spots" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                            @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ (int) old('total_spots', 1) === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-1">{{ __('messages.carpool_note') }}</label>
                        <textarea name="note" maxlength="500" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_note_placeholder') }}">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showOfferForm = false" class="px-4 py-3 text-base rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-[#d1d5db] font-medium">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-3 text-base rounded-lg text-white font-medium" style="background-color: {{ $accentColor }};">
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
                    $st = $event->getStartDateTime($date);
                    $offerExpired = $st && $st->isPast();
                } else {
                    $offerExpired = $eventEnded;
                }
            @endphp
            <div class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-5 mb-4 carpool-offer" data-direction="{{ $offer->direction }}" data-city="{{ strtolower($offer->city) }}" v-show="(filter === 'all' || filter === '{{ $offer->direction }}') && (cityFilter === '' || {{ json_encode(strtolower($offer->city)) }}.includes(cityFilter.toLowerCase()))">

                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $offer->user->name }}</span>
                            @php
                                $driverRating = $carpoolRatings[$offer->user_id]['rating'] ?? null;
                                $driverReviewCount = $carpoolRatings[$offer->user_id]['count'] ?? 0;
                            @endphp
                            @if ($driverReviewCount > 0)
                            <span class="text-xs text-gray-500 dark:text-[#9ca3af] flex items-center gap-0.5">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                                {{ number_format($driverRating, 1) }} ({{ $driverReviewCount }})
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ $offer->city }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $offer->direction === 'to_event' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : ($offer->direction === 'from_event' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                        {{ $offer->directionLabel() }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-[#d1d5db] mb-3">
                    @if ($offer->departure_time)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ \Carbon\Carbon::parse($offer->departure_time)->format('H:i') }}
                    </span>
                    @endif
                    @if ($offer->meeting_point)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $offer->meeting_point }}
                    </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        @php $available = $offer->availableSpots(); @endphp
                        {{ trans_choice('messages.carpool_spots_available', $available, ['count' => $available, 'total' => $offer->total_spots]) }}
                    </span>
                </div>

                @if ($offer->note)
                <p class="text-sm text-gray-500 dark:text-[#9ca3af] mb-3 italic">{{ $offer->note }}</p>
                @endif

                {{-- Contact info for approved riders --}}
                @if ($user && $myRequests->has($offer->id) && $myRequests[$offer->id]->status === 'approved')
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 mb-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-400 mb-1">{{ __('messages.carpool_contact_info') }}</p>
                    <p class="text-sm text-green-700 dark:text-green-300">{{ __('messages.email') }}: {{ $offer->user->email }}</p>
                    @if ($offer->user->phone)
                    <p class="text-sm text-green-700 dark:text-green-300">{{ __('messages.phone') }}: {{ $offer->user->phone }}</p>
                    @endif
                </div>
                @endif

                {{-- Driver's own offer: show pending requests --}}
                @if ($user && $offer->user_id === $user->id)
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-[#9ca3af] uppercase mb-2">{{ __('messages.carpool_your_offer') }}</p>

                    @if ($offer->pendingRequests->count() > 0)
                    <div class="space-y-2 mb-3">
                        @foreach ($offer->pendingRequests as $pendingReq)
                        <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $pendingReq->user->name }}</span>
                                @php
                                    $riderRating = $carpoolRatings[$pendingReq->user_id]['rating'] ?? null;
                                    $riderReviewCount = $carpoolRatings[$pendingReq->user_id]['count'] ?? 0;
                                @endphp
                                @if ($riderReviewCount > 0)
                                <span class="text-xs text-gray-500 dark:text-[#9ca3af] ml-1">
                                    <svg class="w-3 h-3 text-amber-400 inline" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                    {{ number_format($riderRating, 1) }}
                                </span>
                                @endif
                                @if ($pendingReq->message)
                                <p class="text-xs text-gray-500 dark:text-[#9ca3af] mt-0.5 italic">{{ $pendingReq->message }}</p>
                                @endif
                            </div>
                            @if (! $eventEnded)
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('carpool.approve', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'request_hash' => \App\Utils\UrlUtils::encodeId($pendingReq->id)]) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs rounded-md bg-green-600 text-white hover:bg-green-700 font-medium">{{ __('messages.approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('carpool.decline', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'request_hash' => \App\Utils\UrlUtils::encodeId($pendingReq->id)]) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs rounded-md bg-red-600 text-white hover:bg-red-700 font-medium">{{ __('messages.decline') }}</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if ($offer->approvedRequests->count() > 0)
                    <div class="space-y-2 mb-3">
                        @foreach ($offer->approvedRequests as $approvedReq)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $approvedReq->user->name }}</span>
                                    <p class="text-xs text-gray-500 dark:text-[#9ca3af]">{{ $approvedReq->user->email }}@if($approvedReq->user->phone) &middot; {{ $approvedReq->user->phone }}@endif</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-green-600 dark:text-green-400 font-medium">{{ __('messages.approved') }}</span>
                                    @php $riderReportKey = \App\Utils\UrlUtils::encodeId($offer->id) . '_' . \App\Utils\UrlUtils::encodeId($approvedReq->user_id); @endphp
                                    <button type="button" @click="reportRiderKey = reportRiderKey === '{{ $riderReportKey }}' ? null : '{{ $riderReportKey }}'" class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">{{ __('messages.report') }}</button>
                                </div>
                            </div>
                            <div v-show="reportRiderKey === '{{ $riderReportKey }}'" class="mt-2">
                                <form method="POST" action="{{ route('carpool.report', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'user_hash' => \App\Utils\UrlUtils::encodeId($approvedReq->user_id)]) }}">
                                    @csrf
                                    <div class="flex gap-2">
                                        <input type="text" name="reason" required maxlength="1000" class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_report_reason') }}">
                                        <button type="submit" class="px-2.5 py-1.5 text-xs rounded-md bg-red-600 text-white hover:bg-red-700 font-medium">{{ __('messages.report') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if (! $eventEnded)
                    <div class="flex gap-2">
                        <button type="button" @click="editSpotsOfferId = editSpotsOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}' ? null : '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-[#d1d5db] hover:bg-gray-50 dark:hover:bg-[#252526] font-medium">{{ __('messages.carpool_edit_spots') }}</button>
                        <form method="POST" action="{{ route('carpool.cancel_offer', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 text-xs rounded-md border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium" onclick="return confirm({{ Js::from(__('messages.carpool_cancel_offer_confirm')) }})">{{ __('messages.carpool_cancel_offer') }}</button>
                        </form>
                    </div>

                    <div v-show="editSpotsOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="mt-3">
                        <form method="POST" action="{{ route('carpool.update_spots', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="flex items-end gap-2">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-[#9ca3af] mb-1">{{ __('messages.carpool_spots') }}</label>
                                <select name="total_spots" class="px-2 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                                    @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $offer->total_spots === $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="px-2.5 py-1 text-xs rounded-md text-white font-medium" style="background-color: {{ $accentColor }};">{{ __('messages.save') }}</button>
                            <button type="button" @click="editSpotsOfferId = null" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-[#d1d5db] hover:bg-gray-50 dark:hover:bg-[#252526] font-medium">{{ __('messages.cancel') }}</button>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Other user's offer: request/status --}}
                @elseif ($user)
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                    @if ($myRequests->has($offer->id))
                        @php $myRequest = $myRequests[$offer->id]; @endphp
                        @if ($myRequest->status === 'pending')
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">{{ __('messages.carpool_request_pending') }}</span>
                            @if (! $eventEnded)
                            <form method="POST" action="{{ route('carpool.cancel_request', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'request_hash' => \App\Utils\UrlUtils::encodeId($myRequest->id)]) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-[#d1d5db] hover:bg-gray-50 dark:hover:bg-[#252526] font-medium">{{ __('messages.cancel') }}</button>
                            </form>
                            @endif
                        </div>
                        @elseif ($myRequest->status === 'approved')
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ __('messages.carpool_request_approved_status') }}</span>
                            @if (! $eventEnded)
                            <form method="POST" action="{{ route('carpool.cancel_request', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'request_hash' => \App\Utils\UrlUtils::encodeId($myRequest->id)]) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-[#d1d5db] hover:bg-gray-50 dark:hover:bg-[#252526] font-medium" onclick="return confirm({{ Js::from(__('messages.carpool_cancel_request_confirm')) }})">{{ __('messages.carpool_withdraw') }}</button>
                            </form>
                            @endif
                        </div>
                        @elseif ($myRequest->status === 'declined')
                        <div>
                            <span class="text-sm text-red-600 dark:text-red-400 font-medium">{{ __('messages.carpool_request_declined_status') }}</span>
                            @if (! $offerExpired && ! $offer->isFull())
                            <form method="POST" action="{{ route('carpool.request_ride', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="mt-2">
                                @csrf
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <input type="text" name="message" maxlength="500" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_message_placeholder') }}">
                                    </div>
                                    <button type="submit" class="px-3 py-2 text-sm rounded-lg text-white font-medium whitespace-nowrap" style="background-color: {{ $accentColor }};">
                                        {{ __('messages.carpool_request_spot') }}
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                        @endif
                    @elseif (! $offerExpired && ! $offer->isFull())
                    {{-- Request form --}}
                    <form method="POST" action="{{ route('carpool.request_ride', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}">
                        @csrf
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <input type="text" name="message" maxlength="500" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_message_placeholder') }}">
                            </div>
                            <button type="submit" class="px-3 py-2 text-sm rounded-lg text-white font-medium whitespace-nowrap" style="background-color: {{ $accentColor }};">
                                {{ __('messages.carpool_request_spot') }}
                            </button>
                        </div>
                    </form>
                    @elseif ($offer->isFull())
                    <span class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ __('messages.carpool_offer_full') }}</span>
                    @endif

                    {{-- Report link --}}
                    @if ($offer->user_id !== $user->id && $offer->approvedRequests->contains('user_id', $user->id))
                    <div class="mt-2">
                        <button type="button" @click="reportOfferId = reportOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}' ? null : '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">{{ __('messages.report') }}</button>
                        <div v-show="reportOfferId === '{{ \App\Utils\UrlUtils::encodeId($offer->id) }}'" class="mt-2">
                            <form method="POST" action="{{ route('carpool.report', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id), 'user_hash' => \App\Utils\UrlUtils::encodeId($offer->user_id)]) }}">
                                @csrf
                                <div class="flex gap-2">
                                    <input type="text" name="reason" required maxlength="1000" class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_report_reason') }}">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs rounded-md bg-red-600 text-white hover:bg-red-700 font-medium">{{ __('messages.report') }}</button>
                                </div>
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
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('messages.carpool_leave_review') }}</p>
                        @foreach ($reviewableUsers as $reviewUser)
                        <form method="POST" action="{{ route('carpool.store_review', ['subdomain' => $subdomain, 'event_hash' => $eventHash, 'offer_hash' => \App\Utils\UrlUtils::encodeId($offer->id)]) }}" class="mb-2">
                            @csrf
                            <input type="hidden" name="reviewed_user_id" value="{{ \App\Utils\UrlUtils::encodeId($reviewUser->id) }}">
                            <p class="text-xs text-gray-500 dark:text-[#9ca3af] mb-1">{{ __('messages.carpool_review_for', ['name' => $reviewUser->name]) }}</p>
                            <div class="flex items-center gap-2">
                                <select name="rating" required class="px-2 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                                    @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} {{ str_repeat('★', $i) }}</option>
                                    @endfor
                                </select>
                                <input type="text" name="comment" maxlength="1000" class="flex-1 px-2 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-[#2d2d30] dark:text-[#d1d5db] focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]" placeholder="{{ __('messages.carpool_review_comment') }}">
                                <button type="submit" class="px-2.5 py-1 text-xs rounded-md text-white font-medium" style="background-color: {{ $accentColor }};">{{ __('messages.submit') }}</button>
                            </div>
                        </form>
                        @endforeach
                    </div>
                    @endif
                @endif

            </div>
            @empty
            <div class="bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-6 text-center text-gray-500 dark:text-[#9ca3af]">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <p>{{ __('messages.carpool_no_offers') }}</p>
            </div>
            @endforelse

            {{-- Phone nudge --}}
            @if ($user && ! $user->phone)
            <div class="mt-4 bg-white/95 dark:bg-[#2d2d30]/95 backdrop-blur-sm rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-gray-600 dark:text-[#d1d5db]">
                        {{ __('messages.carpool_add_phone') }}
                        <x-link href="{{ app_url(route('profile.edit', [], false)) }}">{{ __('messages.carpool_update_profile') }}</x-link>
                    </p>
                </div>
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
            }).mount('#carpool-app');
        } catch (e) {
            console.error('Carpool Vue mount error:', e);
        }
    }
});
</script>

</x-app-guest-layout>
