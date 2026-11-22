{{-- Whose page this is, at the top of one of the guest portal's form pages
     (partials/guest-form-pages-styles): the schedule's picture, small and never cropped into a
     strip, beside its name. A link to the schedule when $whoLink is given.

     $whoRole  the schedule
     $whoLink  where its name leads, or null for plain text
     $whoName  the name to show, when the page has already worked it out --}}
@php
    $whoName ??= $whoRole->translatedName();
    $whoLink ??= null;
    $whoImage = $whoRole->profile_image_url ? $whoRole->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH) : null;
@endphp
@if ($whoLink)
<a href="{{ $whoLink }}" class="gk-pg-who">
@else
<span class="gk-pg-who">
@endif
    @if ($whoImage)
    <img src="{{ $whoImage }}" alt="" class="gk-pg-avatar" width="48" height="48" decoding="async">
    @endif
    <span class="gk-pg-who-name" dir="{{ content_dir_for_language($whoName, $whoRole->displayLanguageCode()) }}" v-pre>{{ $whoName }}</span>
@if ($whoLink)
</a>
@else
</span>
@endif
