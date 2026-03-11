<x-app-layout :title="__('messages.my_carpools')">

    <x-slot name="meta">
        <meta name="robots" content="noindex, nofollow">
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-[#1e1e1e] py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">

            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('messages.my_carpools') }}</h1>

            {{-- Flash messages --}}
            @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- My offers --}}
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    {{ __('messages.carpool_my_offers') }}
                </h2>

                @forelse ($myOffers as $offer)
                @php
                    $role = $offer->role ?? ($offer->event ? $offer->event->roles->first(fn ($r) => $r->isPro() && $r->carpool_enabled) : null);
                    $subdomain = $role ? $role->subdomain : null;
                @endphp
                @if ($offer->event)
                <div class="bg-white dark:bg-[#2d2d30] rounded-xl shadow-sm border border-gray-200 dark:border-[#2d2d30] p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $offer->event->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ $offer->city }} &middot; {{ $offer->directionLabel() }}</p>
                            <div class="flex gap-3 mt-1 text-xs text-gray-500 dark:text-[#9ca3af]">
                                <span>{{ __('messages.carpool_pending_count', ['count' => $offer->pendingRequests->count()]) }}</span>
                                <span>{{ __('messages.carpool_approved_count', ['count' => $offer->approvedRequests->count()]) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $offer->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $offer->status === 'active' ? __('messages.active') : __('messages.cancelled') }}
                            </span>
                            @if ($subdomain)
                            <a href="{{ config('app.hosted')
                                ? ($offer->event_date
                                    ? route('carpool.index_date', ['subdomain' => $subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($offer->event_id), 'date' => $offer->event_date->format('Y-m-d')])
                                    : route('carpool.index', ['subdomain' => $subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($offer->event_id)]))
                                : '/' . $subdomain . '/carpool/' . \App\Utils\UrlUtils::encodeId($offer->event_id) . ($offer->event_date ? '/' . $offer->event_date->format('Y-m-d') : '') }}"
                               class="text-xs text-[var(--brand-blue)] hover:underline font-medium">{{ __('messages.view') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <p class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ __('messages.carpool_no_offers_yet') }}</p>
                @endforelse
            </div>

            {{-- My requests --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.carpool_my_requests') }}
                </h2>

                @forelse ($myRequests as $request)
                @php
                    $offer = $request->offer;
                    $role = $offer ? ($offer->role ?? ($offer->event ? $offer->event->roles->first(fn ($r) => $r->isPro() && $r->carpool_enabled) : null)) : null;
                    $subdomain = $role ? $role->subdomain : null;
                @endphp
                @if ($offer && $offer->event)
                <div class="bg-white dark:bg-[#2d2d30] rounded-xl shadow-sm border border-gray-200 dark:border-[#2d2d30] p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $offer->event->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-[#9ca3af]">
                                {{ __('messages.carpool_driver') }}: {{ $offer->user->name }} &middot; {{ $offer->city }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                   ($request->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' :
                                   ($request->status === 'declined' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                                   'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400')) }}">
                                {{ __('messages.carpool_status_' . $request->status) }}
                            </span>
                            @if ($subdomain)
                            <a href="{{ config('app.hosted')
                                ? ($offer->event_date
                                    ? route('carpool.index_date', ['subdomain' => $subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($offer->event_id), 'date' => $offer->event_date->format('Y-m-d')])
                                    : route('carpool.index', ['subdomain' => $subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($offer->event_id)]))
                                : '/' . $subdomain . '/carpool/' . \App\Utils\UrlUtils::encodeId($offer->event_id) . ($offer->event_date ? '/' . $offer->event_date->format('Y-m-d') : '') }}"
                               class="text-xs text-[var(--brand-blue)] hover:underline font-medium">{{ __('messages.view') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <p class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ __('messages.carpool_no_requests_yet') }}</p>
                @endforelse
            </div>

        </div>
    </div>

</x-app-layout>
