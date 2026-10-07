<x-app-admin-layout>

    {{-- The rides this person offered and the rides they asked for. It is reached from the
         portal's own menu and only by somebody signed in (the route sits in the auth group), yet
         it was drawn on the bare shell: no sidebar, no header, no way to anywhere else. --}}
    @php
        // An offer outlives its event's row for a moment when an event is deleted; such a line
        // has nothing to show, and counting it would hide the empty state behind an empty list.
        $offers = $myOffers->filter(fn ($offer) => $offer->event)->values();
        $requests = $myRequests->filter(fn ($ride) => $ride->offer && $ride->offer->event)->values();

        // The event's carpool page, where a driver answers riders and a rider sees where to meet.
        $carpoolUrl = function ($offer) {
            $role = $offer->role ?? $offer->event->roles->first(fn ($r) => $r->isPro() && $r->carpool_enabled);

            if (! $role) {
                return null;
            }

            $hash = \App\Utils\UrlUtils::encodeId($offer->event_id);
            $date = $offer->event_date ? $offer->event_date->format('Y-m-d') : null;

            if (! config('app.hosted')) {
                return url('/'.$role->subdomain.'/carpool/'.$hash.($date ? '/'.$date : ''));
            }

            return $date
                ? route('carpool.index_date', ['subdomain' => $role->subdomain, 'event_hash' => $hash, 'date' => $date])
                : route('carpool.index', ['subdomain' => $role->subdomain, 'event_hash' => $hash]);
        };

        $rideDay = fn ($offer) => $offer->event->localStartsAt(true, $offer->event_date ? $offer->event_date->format('Y-m-d') : null);

        $requestTones = ['approved' => 'is-on', 'pending' => 'is-warn', 'declined' => 'is-bad'];
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.my_carpools')" :lead="__('messages.my_carpools_lead')" />

        <x-page-flash :keys="['success' => 'success']" class="mb-4" />

        <div class="page-stack">
            <x-page-card :title="__('messages.carpool_my_offers')" flush id="carpool-offers">
                @if ($offers->isNotEmpty())
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.event') }}</th>
                            <th scope="col">{{ __('messages.from') }}</th>
                            <th scope="col">{{ __('messages.carpool_riders') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offers as $offer)
                        @php $url = $carpoolUrl($offer); @endphp
                        <tr>
                            <td class="c-main c-strong">
                                <bdi>{{ $offer->event->name }}</bdi>
                                <span class="c-sub">{{ $rideDay($offer) }}</span>
                            </td>
                            <td class="c-wrap">
                                <bdi>{{ $offer->city }}</bdi>
                                <span class="c-sub">{{ $offer->directionLabel() }}</span>
                            </td>
                            <td>
                                {{ __('messages.carpool_approved_count', ['count' => $offer->approvedRequests->count()]) }}
                                @if ($offer->pendingRequests->count() > 0)
                                <span class="c-sub">{{ __('messages.carpool_pending_count', ['count' => $offer->pendingRequests->count()]) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="event-status {{ $offer->status === 'active' ? 'is-on' : '' }}">{{ $offer->status === 'active' ? __('messages.active') : __('messages.cancelled') }}</span>
                            </td>
                            <td class="c-actions">
                                @if ($url)
                                <a href="{{ $url }}" class="page-tool">{{ __('messages.view') }}</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <x-page-empty compact :title="__('messages.carpool_no_offers_yet')" :text="__('messages.carpool_offers_empty_help')" />
                @endif
            </x-page-card>

            <x-page-card :title="__('messages.carpool_my_requests')" flush id="carpool-requests">
                @if ($requests->isNotEmpty())
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.event') }}</th>
                            <th scope="col">{{ __('messages.carpool_driver') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $ride)
                        @php
                            $offer = $ride->offer;
                            $url = $carpoolUrl($offer);
                        @endphp
                        <tr>
                            <td class="c-main c-strong">
                                <bdi>{{ $offer->event->name }}</bdi>
                                <span class="c-sub">{{ $rideDay($offer) }}</span>
                            </td>
                            <td class="c-wrap">
                                <bdi>{{ $offer->user?->name }}</bdi>
                                <span class="c-sub"><bdi>{{ $offer->city }}</bdi> &middot; {{ $offer->directionLabel() }}</span>
                            </td>
                            <td>
                                <span class="event-status {{ $requestTones[$ride->status] ?? '' }}">{{ __('messages.carpool_status_' . $ride->status) }}</span>
                            </td>
                            <td class="c-actions">
                                @if ($url)
                                <a href="{{ $url }}" class="page-tool">{{ __('messages.view') }}</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <x-page-empty compact :title="__('messages.carpool_no_requests_yet')" :text="__('messages.carpool_requests_empty_help')" />
                @endif
            </x-page-card>
        </div>
    </div>

</x-app-admin-layout>
