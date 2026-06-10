@php
    $onEventPage = $onEventPage ?? false;
    $bannerHtml = ($role->isPro() && $role->banner_enabled)
        ? trim((string) $role->translatedBannerMessage())
        : '';
    $showBanner = $bannerHtml !== '' && ! request()->embed && ! request()->graphic
        && (! $onEventPage || $role->banner_on_event_pages);
@endphp
@if ($showBanner)
<div class="bg-blue-50 dark:bg-blue-950 border-b border-blue-200 dark:border-blue-800 py-3">
  <div class="container mx-auto px-5">
    <div class="gp-banner text-center text-sm sm:text-base text-blue-800 dark:text-blue-200" dir="auto">
      {!! \App\Utils\UrlUtils::convertUrlsToLinks($bannerHtml) !!}
    </div>
  </div>
</div>
@endif
