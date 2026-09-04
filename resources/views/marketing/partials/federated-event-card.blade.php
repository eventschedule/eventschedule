@php
    // Everything here is third-party content received from another install. It is
    // escaped by Blade, and this page is deliberately NOT a Vue mount - if that ever
    // changes, these values must be wrapped in <x-user-text> (or passed as @json
    // props), because Vue's runtime compiler would otherwise treat a name containing
    // a mustache expression as a template and execute it.
    $sourceHost = $event->sourceHost();
    $location = $event->locationLabel();
    $hash = \App\Utils\UrlUtils::encodeId($event->id);
@endphp
<div class="group relative flex flex-col bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 hover:shadow-lg hover:border-blue-400/50 dark:hover:border-blue-400/30 transition-all overflow-hidden">

    {{-- Image header. Always a locally stored copy: hotlinking would leak visitor
         IPs to arbitrary third-party hosts and need img-src opened up. --}}
    <div class="relative aspect-[16/9] bg-gray-100 dark:bg-white/5 overflow-hidden">
        <img src="{{ $event->imageUrl() }}" alt="{{ $event->name }}"
             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
             width="640" height="360" loading="lazy" decoding="async">

        @if($sourceHost)
            <span class="absolute bottom-2 ltr:left-2 rtl:right-2 z-20 rounded-full bg-black/60 px-2.5 py-0.5 text-xs font-medium text-white backdrop-blur-md">
                {{ $sourceHost }}
            </span>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-5 flex flex-col flex-1">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3 line-clamp-2">{{ $event->name }}</h3>

        <div class="mt-auto flex flex-col gap-2">
            @if($event->next_occurrence_at)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{-- Rendered in the event's own timezone: an event's time is a property
                         of where it happens. Formatting the UTC instant in the viewer's zone
                         shifts evening events by a day.

                         safeTimezone() rather than the raw column: this is third-party
                         data on a public page, and setTimezone() throws on an unknown
                         zone, which would 500 the whole page rather than one card. --}}
                    @php $zone = $event->safeTimezone(); @endphp
                    <span class="truncate">
                        {{ $event->next_occurrence_at->copy()->setTimezone($zone)->format('M j, Y g:ia') }}
                        <span class="text-gray-500 dark:text-gray-400">{{ $event->next_occurrence_at->copy()->setTimezone($zone)->format('T') }}</span>
                    </span>
                </div>
            @endif

            {{-- "Online" comes from the sender's own flag, not from the absence of a
                 venue: an in-person event whose sender simply sent no venue data is
                 not online, and used to be labelled as though it were. With neither
                 signal the line is left empty rather than guessed at. --}}
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @if($location)
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate">{{ $location }}</span>
                @elseif($event->isOnline())
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span class="truncate">{{ __('messages.online') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Stretched link straight to the origin.

         Deliberately dofollow: the backlink is the entire value federation offers
         operators, and rel="nofollow" would silently void it. The data attribute is
         read by a delegated click handler to count the visit; the href itself is
         never a tracking redirect, which would point this at our own domain. --}}
    <a href="{{ $event->url }}" target="_blank" rel="noopener"
       data-federated-click="{{ $hash }}"
       class="absolute inset-0 z-10" aria-label="{{ $event->name }}"></a>

    @if(auth()->check() && auth()->user()->isAdmin())
        <form method="POST" action="{{ route('marketing.federation.block', $hash) }}" class="absolute top-2 ltr:right-2 rtl:left-2 z-20">
            @csrf
            {{-- English literal, matching the local card: the marketing site is
                 intentionally English-only. --}}
            <button type="submit"
                    aria-label="Hide this listing from discovery"
                    class="inline-flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-md transition-all hover:bg-black/80">
                Hide
            </button>
        </form>
    @endif
</div>
