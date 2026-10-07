{{-- The schedule's other upcoming events, down the left column of an event page under the
     flyer, the performers and the venue: a date set between two lines, then a small card for
     each event that day, with its picture where it has one. On a phone it is the last thing on
     the page and stops after five.

     This is what the page always had there, and what gives a visitor somewhere to go next
     without going back. For a few days in October 2026 it was three rows across the foot of the
     page instead; it read as a footer and the column beside the story stood empty.

     What did change stays changed: it is drawn by the server from EventRepo::upcomingForGuest()
     (RoleController::getEvent() hands over up to twenty). It used to be a second copy of the
     calendar's Vue app, which fetched up to 400 events and built twenty cards from them in
     the browser, with a footer link decided half here and half there.

     Each card is a real link. $moreEvents is [event, date] pairs; the date is in the event's
     schedule timezone, like every other date here. Names are user text and this is not inside a
     Vue mount, so Blade's escaping is what guards them. --}}
@if ($moreEvents->isNotEmpty())
<section id="gp-upcoming-events" class="gk-o10 gk-panel gk-panel-flush gk-up bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm {{ $role->isRtl() ? 'rtl' : '' }}" aria-labelledby="es-more-events-title">
  <h2 id="es-more-events-title" class="gk-up-title">{{ $role->customLabel('events') }}</h2>
  @php $upDay = null; @endphp
  <ul class="gk-up-list">
    @foreach ($moreEvents as $row)
      @php
        $rowEvent = $row['event'];
        $rowStart = $rowEvent->getStartDateTime($row['date'], true, $rowEvent->scheduleTimezone());
        $rowName = $rowEvent->nameInLanguage($displayLang, $role);
        $rowWhere = $rowEvent->getVenueDisplayName(true, $displayLang);
        $rowImage = $rowEvent->flyer_image_url ? $rowEvent->getImageUrl(480) : null;
        $rowTime = $rowEvent->getStartEndTime($row['date'], get_use_24_hour_time($role));
        // The filter the visitor arrived with rides along, as it did on the list this replaces.
        $rowUrl = $rowEvent->getGuestUrl($role->subdomain, $rowEvent->days_of_week ? $row['date'] : null);
        $rowUrl .= empty($filterQuery) ? '' : (str_contains($rowUrl, '?') ? '&' : '?').http_build_query($filterQuery);
        // A phone gets the first five: the page is long enough there.
        $rowLate = $loop->index >= 5 ? 'gk-up-late' : '';
        $rowDay = $rowStart->format('Y-m-d');
      @endphp
      @if ($rowDay !== $upDay)
        @php $upDay = $rowDay; @endphp
        <li class="gk-up-day {{ $rowLate }}"><time datetime="{{ $rowDay }}">{{ \App\Utils\DateUtils::dayLabel($rowStart) }}</time></li>
      @endif
      <li class="{{ $rowLate }}">
        <a class="gk-up-card {{ $rowImage ? 'gk-up-pictured' : '' }}" href="{{ $rowUrl }}">
          <span class="gk-up-body">
            <span class="gk-up-name" dir="{{ content_dir_for_language($rowName, $displayLang) }}">{{ $rowName }}</span>
            @if ($rowWhere)
              <span class="gk-up-meta">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" /></svg>
                <span>{{ $rowWhere }}</span>
              </span>
            @endif
            @if ($rowTime)
              <span class="gk-up-meta">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z" /></svg>
                <bdi dir="ltr">{{ $rowTime }}</bdi>
              </span>
            @endif
          </span>
          @if ($rowImage)
            <img class="gk-up-img" src="{{ $rowImage }}" alt="" width="160" height="160" loading="lazy" decoding="async">
          @endif
        </a>
      </li>
    @endforeach
  </ul>
  <a class="gk-link gk-up-all" href="{{ $backUrl }}">{{ $role->customLabel('view_full_schedule') }}</a>
</section>
@endif
