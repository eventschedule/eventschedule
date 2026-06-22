{{--
    Content strip rendered beneath the compact header bar so a slim bar does not
    drop the schedule's description or social/contact links. Expects: $role, $isRtl, and
    optional $onDark. The whole strip is v-pre so user-supplied description HTML is never
    compiled as a Vue template (CSTI guard).
--}}
@php
    $onDark = $onDark ?? false;
    $hasEmail = $role->email && $role->show_email;
    $hasPhone = $role->phone && $role->show_phone && $role->phone_verified_at;
    $hasWebsite = $role->website;
    $hasSocial = $role->social_links && $role->social_links != '[]';
    $hasPayment = $role->payment_links && $role->payment_links != '[]';
    $hasContact = $hasEmail || $hasPhone || $hasWebsite || $hasSocial || $hasPayment;
    $hasDescription = (bool) $role->translatedDescription();
    $iconClass = $onDark
        ? 'text-white/70 hover:text-white'
        : 'text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200';
@endphp
@if ($hasDescription || $hasContact || $role->isVenue())
<div class="py-3 border-t {{ $onDark ? 'border-white/10' : 'border-gray-200 dark:border-gray-700' }} flex flex-col sm:flex-row sm:items-start gap-x-6 gap-y-3 {{ $isRtl ? 'rtl' : '' }}" v-pre>
    @if ($hasDescription)
    <details class="es-desc min-w-0 flex-1">
        <summary class="es-desc-summary cursor-pointer flex items-start gap-1.5 text-sm {{ $onDark ? 'text-white/80' : 'text-[#33383C] dark:text-gray-300' }}">
            <span class="es-desc-text custom-content min-w-0 flex-1">{!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}</span>
            <svg class="es-desc-chevron w-4 h-4 mt-0.5 flex-shrink-0 transition-transform duration-200 {{ $onDark ? 'text-white/50' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
    </details>
    @endif
    @if ($hasContact || $role->isVenue())
    <div class="flex flex-row flex-wrap gap-4 items-center {{ $hasDescription ? ($isRtl ? 'sm:mr-auto' : 'sm:ml-auto') : '' }}">
        @if($role->isVenue())
        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
           target="_blank" rel="noopener noreferrer nofollow"
           class="inline-flex items-center gap-1.5 text-sm {{ $iconClass }} transition-colors">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/></svg>
            <span class="truncate max-w-[220px]">{{ $role->shortAddress() }}</span>
        </a>
        @endif
        @if($hasEmail)
        <a href="mailto:{{ $role->email }}" class="{{ $iconClass }} transition-colors social-tooltip" data-tooltip="Email: {{ $role->email }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/></svg>
        </a>
        @endif
        @if($hasPhone)
        <a href="tel:{{ $role->phone }}" class="{{ $iconClass }} transition-colors social-tooltip" data-tooltip="Phone: {{ $role->phone }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
        </a>
        @endif
        @if($hasWebsite)
        <a href="{{ $role->website }}" target="_blank" rel="noopener noreferrer nofollow" class="{{ $iconClass }} transition-colors social-tooltip" data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/></svg>
        </a>
        @endif
        @if($hasSocial)
            @foreach ($role->decodeLinks('social_links') as $link)
            @php $gpBelowPlatform = \App\Utils\UrlUtils::detectPlatform($link->url); @endphp
            <a href="{{ $gpBelowPlatform !== 'website' ? $role->getGuestUrl() . '/' . $gpBelowPlatform : $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
               class="{{ $iconClass }} transition-colors social-tooltip" data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                <x-url-icon class="w-5 h-5" color="currentColor">{{ \App\Utils\UrlUtils::clean($link->url) }}</x-url-icon>
            </a>
            @endforeach
        @endif
        @if($hasPayment)
            @foreach ($role->decodeLinks('payment_links') as $link)
            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
               class="{{ $iconClass }} transition-colors social-tooltip" data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                <x-url-icon class="w-5 h-5" color="currentColor">{{ \App\Utils\UrlUtils::clean($link->url) }}</x-url-icon>
            </a>
            @endforeach
        @endif
    </div>
    @endif
</div>
<style {!! nonce_attr() !!}>
.es-desc-summary { list-style: none; }
.es-desc-summary::-webkit-details-marker { display: none; }
.es-desc:not([open]) .es-desc-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.es-desc[open] .es-desc-chevron { transform: rotate(180deg); }
</style>
@endif
