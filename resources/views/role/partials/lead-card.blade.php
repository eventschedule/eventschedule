{{-- One of the events the month leads with (role/show-guest, #gp-next-event): the first is the
     large card, picture beside its words; the next two are compact, picture above. Expects
     $leadRow (['event', 'date']), $leadCompact, $leadCategory, $role, and $selectedGroup when the
     page is a sub-schedule's.

     Its picture has a box of its own shape from the start, so nothing moves when it arrives, and
     it is lazy: in the list view the block is display:none, and a picture that is not lazy is
     fetched all the same. --}}
@php
    $leadEvent = $leadRow['event'];
    $leadLang = $role->displayLanguageCode();
    $leadZone = $leadEvent->scheduleTimezone();
    $leadStart = $leadEvent->getStartDateTime($leadRow['date'], true, $leadZone);
    $leadToday = \Carbon\Carbon::now($leadZone)->format('Y-m-d');
    $leadDay = $leadStart->format('Y-m-d');
    $leadWord = $leadDay === $leadToday ? __('messages.today')
        : ($leadDay === \Carbon\Carbon::now($leadZone)->addDay()->format('Y-m-d') ? __('messages.tomorrow') : null);
    $leadName = $leadEvent->nameInLanguage($leadLang, $role);
    // The place as the list names it, unless this IS the place's own schedule.
    $leadWhere = ($leadEvent->venue && $leadEvent->venue->id === $role->id) ? null : ($leadEvent->getVenueDisplayName(true, $leadLang) ?: null);
    $leadTime = $leadEvent->getStartEndTime($leadRow['date'], get_use_24_hour_time($role));
    $leadImage = $leadEvent->flyer_image_url ? $leadEvent->getImageUrl(960) : null;
    // ONE query, for this one event's tickets, said here so it is not a side effect of reading
    // them: the fifty events of $upcoming are deliberately loaded without theirs
    // (GuestScheduleSchemaTest holds that the list never costs a query an event).
    $leadEvent->loadMissing('tickets');
    // The address keeps what the page was narrowed by, as a row's does, so the event's way back
    // returns here.
    $leadQuery = array_filter([
        'category' => $leadCategory,
        'schedule' => (isset($selectedGroup) && $selectedGroup) ? $selectedGroup->slug : null,
        'layout' => requested_event_layout(),
    ], fn ($value) => $value !== null && $value !== '');
    $leadUrl = $leadEvent->getGuestUrl($role->subdomain, $leadEvent->days_of_week ? $leadRow['date'] : null);
    $leadUrl .= $leadQuery ? (str_contains($leadUrl, '?') ? '&' : '?').http_build_query($leadQuery) : '';
@endphp
<a class="gk-panel gk-lead {{ $leadCompact ? 'gk-lead-compact' : '' }} {{ $leadImage ? '' : 'gk-lead-bare' }} {{ $role->isRtl() ? 'rtl' : '' }}"
   href="{{ $leadUrl }}" data-funnel="list_tap">
    @if ($leadImage)
        <img class="gk-lead-img" src="{{ $leadImage }}" alt="" width="960" height="540" loading="lazy" decoding="async">
    @endif
    <span class="gk-lead-body">
        <span class="gk-lead-when">
            @if ($leadWord)<b>{{ $leadWord }}</b>@endif
            <time datetime="{{ $leadStart->format('Y-m-d\TH:i:sP') }}">{{ \App\Utils\DateUtils::dayLabel($leadStart) }}</time>
        </span>
        <span class="gk-lead-title" dir="{{ content_dir_for_language($leadName, $leadLang) }}">{{ $leadName }}</span>
        <span class="gk-lead-where">
            @if ($leadTime)<bdi dir="ltr">{{ $leadTime }}</bdi>@endif
            @if ($leadWhere)<span>{{ $leadWhere }}</span>@endif
        </span>
        @include('partials.guest-ticket-chips', ['chipEvent' => $leadEvent, 'chipDate' => $leadRow['date'], 'chipRole' => $role])
    </span>
</a>
