{{-- The schedule's other upcoming events, down the left column of an event page under the
     flyer, the performers and the venue. They look as the schedule's own list does on a phone:
     a panel for each day under its date, a row for each event with its time, its name, what it
     costs and whether any are left, and its picture where it has one. Where the page is one
     column (a phone, a tablet) the list comes after everything but the free tier's "create
     your own", and stops after five.

     The column is where this list always was. For a few days in October 2026 it was three rows
     across the foot of the page, and then small cards under a date set between two lines; the
     schedule page's phone rows are what it was asked to match, so it is drawn with their own
     classes (partials/guest-kit-styles: .gk-day, .gk-row) and says what they say
     (partials/guest-ticket-chips).

     It is drawn by the server from EventRepo::upcomingForGuest() (RoleController::viewGuest()
     hands over up to twenty, with their tickets loaded in ONE query for the chips). It used to
     be a second copy of the calendar's Vue app, which fetched up to 400 events and built
     twenty cards from them in the browser.

     Each row is a real link. $moreEvents is [event, date] pairs; the date is in the event's
     schedule timezone, like every other date here. Names are user text and this is not inside a
     Vue mount, so Blade's escaping is what guards them. --}}
@if ($moreEvents->isNotEmpty())
@php
    $upUse24 = get_use_24_hour_time($role);
    // Day by day, in the order the list came in. A phone and a tablet get the first five events.
    $upDays = [];
    foreach ($moreEvents as $upIndex => $upRow) {
        $upStart = $upRow['event']->getStartDateTime($upRow['date'], true, $upRow['event']->scheduleTimezone());
        $upDays[$upStart->format('Y-m-d')][] = ['row' => $upRow, 'start' => $upStart, 'late' => $upIndex >= 5];
    }
@endphp
<section id="gp-upcoming-events" class="gk-o10 gk-up {{ $role->isRtl() ? 'rtl' : '' }}" aria-labelledby="es-more-events-title">
  <div class="gk-panel gk-panel-flush gk-up-head bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
    <h2 id="es-more-events-title" class="gk-up-title">{{ $role->customLabel('events') }}</h2>
    <a class="gk-link gk-up-all" href="{{ $backUrl }}">{{ $role->customLabel('view_full_schedule') }}</a>
  </div>
  @foreach ($upDays as $upDay => $upRows)
    @php
      $upFirst = $upRows[0]['start'];
      $upToday = \Carbon\Carbon::now($upFirst->getTimezone());
      $upWord = $upDay === $upToday->format('Y-m-d') ? __('messages.today')
          : ($upDay === $upToday->copy()->addDay()->format('Y-m-d') ? __('messages.tomorrow') : null);
      $upAllLate = collect($upRows)->every(fn ($entry) => $entry['late']);
    @endphp
    <div class="gk-panel gk-panel-flush gk-day bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm {{ $upAllLate ? 'gk-up-late' : '' }}" data-up-day="{{ $upDay }}">
      <div class="gk-dayhead">
        @if ($upWord)<span class="gk-dayhead-word">{{ $upWord }}</span>@endif
        <h3 class="gk-dayhead-title"><time datetime="{{ $upDay }}">{{ \App\Utils\DateUtils::dayLabel($upFirst) }}</time></h3>
      </div>
      <ul class="gk-rows">
        @foreach ($upRows as $upEntry)
          @php
            $rowEvent = $upEntry['row']['event'];
            $rowDate = $upEntry['row']['date'];
            $rowStart = $upEntry['start'];
            $rowName = $rowEvent->nameInLanguage($displayLang, $role);
            $rowAbout = $rowEvent->shortDescriptionInLanguage($displayLang, $role);
            // The place, unless this IS the place's own schedule: every row would say it.
            $rowWhere = ($rowEvent->venue && $rowEvent->venue->id === $role->id) ? '' : $rowEvent->getVenueDisplayName(true, $displayLang);
            $rowImage = $rowEvent->flyer_image_url ? $rowEvent->getImageUrl(480) : null;
            // When it starts; an event over several days says which days.
            $rowTime = $rowEvent->is_multi_day
                ? $rowStart->translatedFormat('M j').' - '.$rowEvent->getEndDateTime($rowDate, true, $rowEvent->scheduleTimezone())->translatedFormat('M j')
                : $rowStart->format($upUse24 ? 'H:i' : 'g:i A');
            // The filter the visitor arrived with rides along, as it did on the list this replaces.
            $rowUrl = $rowEvent->getGuestUrl($role->subdomain, $rowEvent->days_of_week ? $rowDate : null);
            $rowUrl .= empty($filterQuery) ? '' : (str_contains($rowUrl, '?') ? '&' : '?').http_build_query($filterQuery);
          @endphp
          <li class="gk-row-item {{ $upEntry['late'] ? 'gk-up-late' : '' }}">
            <a class="gk-row gk-row-stack {{ $rowImage ? '' : 'gk-row-stack-bare' }}" href="{{ $rowUrl }}">
              <span class="gk-row-time"><bdi dir="ltr">{{ $rowTime }}</bdi></span>
              <span class="gk-row-body">
                <span class="gk-row-title" dir="{{ content_dir_for_language($rowName, $displayLang) }}">{{ $rowName }}</span>
                @if ($rowAbout)
                  <span class="gk-row-desc" dir="{{ content_dir_for_language($rowAbout, $displayLang) }}">{{ $rowAbout }}</span>
                @endif
                @if ($rowWhere)
                  <span class="gk-row-where">{{ $rowWhere }}</span>
                @endif
                @include('partials.guest-ticket-chips', ['chipEvent' => $rowEvent, 'chipDate' => $rowDate, 'chipRole' => $role])
              </span>
              @if ($rowImage)
                <span class="gk-row-media"><img class="gk-row-img" src="{{ $rowImage }}" alt="" width="76" height="76" loading="lazy" decoding="async"></span>
              @endif
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  @endforeach
</section>
@endif
