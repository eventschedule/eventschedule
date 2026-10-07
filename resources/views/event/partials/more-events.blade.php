{{-- "More events" at the foot of an event page: the schedule's next three other events, as rows.

     Drawn by the server from EventRepo::upcomingForGuest(). It replaces a second copy of the
     calendar app in the page's side column, which fetched up to 400 events and built up to 20
     cards from them to show a short list, and which on a phone was more than half the page.

     Each row is a real link. $moreEvents is [event, date] pairs; the date is in the event's
     schedule timezone, like every other date here. Names are user text and this is not inside a
     Vue mount, so Blade's escaping is what guards them. --}}
@if ($moreEvents->isNotEmpty())
<section id="gp-upcoming-events" class="gk-panel gk-panel-flush bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm {{ $role->isRtl() ? 'rtl' : '' }}" aria-labelledby="es-more-events-title">
  <div class="gk-dayhead">
    <h2 id="es-more-events-title">{{ $role->customLabel('events') }}</h2>
    <a class="gk-link gk-dayhead-link" href="{{ $backUrl }}">{{ $role->customLabel('view_full_schedule') }}</a>
  </div>
  <ul class="gk-rows">
    @foreach ($moreEvents as $row)
      @php
        $rowEvent = $row['event'];
        $rowStart = $rowEvent->getStartDateTime($row['date'], true, $rowEvent->scheduleTimezone());
        $rowName = $rowEvent->nameInLanguage($displayLang, $role);
        $rowVenue = $rowEvent->venue;
        $rowWhere = $rowVenue
            ? implode(', ', array_filter([$rowVenue->nameInLanguage($displayLang), $rowVenue->translatedCity()]))
            : ($rowEvent->event_url ? __('messages.online') : '');
        $rowImage = $rowEvent->flyer_image_url ? $rowEvent->getImageUrl(480) : null;
        $rowTime = $rowEvent->getStartEndTime($row['date'], get_use_24_hour_time($role));
        // The filter the visitor arrived with rides along, as it did on the list this replaces.
        $rowUrl = $rowEvent->getGuestUrl($role->subdomain, $rowEvent->days_of_week ? $row['date'] : null);
        $rowUrl .= empty($filterQuery) ? '' : (str_contains($rowUrl, '?') ? '&' : '?').http_build_query($filterQuery);
      @endphp
      <li>
        <a class="gk-row {{ $rowImage ? '' : 'gk-row-bare' }}" href="{{ $rowUrl }}">
          <span class="gk-row-time">
            <time datetime="{{ $rowStart->format('Y-m-d\TH:i:sP') }}">{{ $rowStart->translatedFormat($rowStart->isCurrentYear() ? 'D, M j' : 'D, M j, Y') }}</time>
            @if ($rowTime)
              <span><bdi dir="ltr">{{ $rowTime }}</bdi></span>
            @endif
          </span>
          <span class="gk-row-body">
            <span class="gk-row-title" dir="{{ content_dir_for_language($rowName, $displayLang) }}">{{ $rowName }}</span>
            @if ($rowWhere)
              <span class="gk-row-where">{{ $rowWhere }}</span>
            @endif
          </span>
          @if ($rowImage)
            <img class="gk-row-img" src="{{ $rowImage }}" alt="" width="76" height="76" loading="lazy" decoding="async">
          @endif
        </a>
      </li>
    @endforeach
  </ul>
</section>
@endif
