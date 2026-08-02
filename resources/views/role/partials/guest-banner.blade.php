@php
    $onEventPage = $onEventPage ?? false;
    $bannerHtml = ($role->isPro() && $role->banner_enabled)
        ? trim((string) $role->translatedBannerMessage())
        : '';
    $showBanner = $bannerHtml !== '' && ! request()->embed && ! request()->graphic
        && (! $onEventPage || $role->banner_on_event_pages);
@endphp
@if ($showBanner)
{{-- relative z-20: the mobile background overlay in role/show-guest.blade.php lives inside a
     "relative z-10" container, so its own -z-10 is trapped in that stacking context and paints
     over this bar. Clearing z-10 keeps the bar visible on mobile, while the language switcher
     (z-50) and the fixed event status bars (z-[60]) still sit above it.
     mt-4: on event pages this bar follows the language switcher (or the fixed-status spacer)
     rather than opening the page, so it needs the gap the switcher's pt-4 gives it elsewhere. --}}
<div class="relative z-20 {{ $onEventPage ? 'mt-4' : '' }} bg-blue-50 dark:bg-blue-950 border-b border-blue-200 dark:border-blue-800 py-3">
  <div class="container mx-auto px-5">
    <div class="gp-banner text-center text-sm sm:text-base text-blue-800 dark:text-blue-200" dir="auto">
      {!! \App\Utils\UrlUtils::convertUrlsToLinks($bannerHtml) !!}
    </div>
  </div>
</div>
@endif
