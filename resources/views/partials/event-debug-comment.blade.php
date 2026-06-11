{{--
  TEMPORARY timezone diagnostic. Renders an HTML comment (invisible to users,
  visible in "View Source") exposing how a single event's stored UTC time maps
  into each relevant timezone, to debug the event-graphics wrong day/time issue.
  Search the page source for: EVENT-TZ-DEBUG
  Params: $event (required), $contextRole (the schedule whose page/graphic this is),
          $compact (default false - one line per event for list pages).
  Remove once the timezone root cause is fixed.
--}}
@php
  $tzFmt = fn ($tz) => rescue(fn () => $event->getStartDateTime(null, true, $tz ?: 'UTC')->format('D Y-m-d H:i'), 'ERR', false);
  $isCompact = $compact ?? false;
  $ctx = (isset($contextRole) && $contextRole) ? $contextRole : null;
  $creatorTz = $event->user?->timezone;

  if ($isCompact) {
      $d = ['EVENT-TZ-DEBUG ' . \App\Utils\UrlUtils::encodeId($event->id) . ' "' . $event->name . '"'
          . ' raw=' . $event->starts_at
          . ' dateonly=' . (strlen((string) $event->starts_at) === 10 ? 'Y' : 'N')
          . ' | intended(' . ($creatorTz ?? 'NULL') . ')=' . $tzFmt($creatorTz)
          . ($ctx ? ' | graphic(' . ($ctx->timezone ?? 'NULL') . ')=' . $tzFmt($ctx->timezone) : '')];
  } else {
      $ctxId = $ctx ? $ctx->id : null;
      $sched = fn ($r, $label) => $label . ($ctxId === $r->id ? ' *graphic*' : '') . ': "' . $r->name
          . '" [' . $r->type . '] sub=' . $r->subdomain . ' tz=' . ($r->timezone ?? 'NULL')
          . ' 24h=' . ($r->use_24_hour_time ? 'Y' : 'N') . ' lang=' . ($r->language_code ?? '-')
          . ' -> ' . $tzFmt($r->timezone);
      $d = [];
      $d[] = 'EVENT-TZ-DEBUG ' . \App\Utils\UrlUtils::encodeId($event->id) . ' "' . $event->name . '"';
      $d[] = 'starts_at(raw/UTC): ' . $event->starts_at . '  dur: ' . $event->duration
          . '  date_only: ' . (strlen((string) $event->starts_at) === 10 ? 'Y' : 'N')
          . '  multi_day: ' . ($event->is_multi_day ? 'Y' : 'N')
          . '  recurring: ' . ($event->days_of_week ? 'Y' : 'N');
      $d[] = 'INTENDED creator tz=' . ($creatorTz ?? 'NULL') . ' -> ' . $tzFmt($creatorTz);
      if ($ctx) { $d[] = 'GRAPHIC  ctx tz=' . ($ctx->timezone ?? 'NULL') . ' -> ' . $tzFmt($ctx->timezone); }
      if ($event->creatorRole) { $d[] = $sched($event->creatorRole, 'creatorRole'); }
      foreach ($event->roles as $r) { $d[] = $sched($r, 'role'); }
      $u = auth()->user();
      $d[] = 'viewer tz: ' . ($u ? ($u->timezone ?? 'NULL') . ' -> ' . $tzFmt($u->timezone) : 'guest');
      $d[] = 'app.tz: ' . config('app.timezone') . '  now_utc: ' . now('UTC')->format('Y-m-d H:i')
          . '  localStartsAt: ' . strip_tags($event->localStartsAt(true));
  }
@endphp
<!--
{!! implode("\n", array_map('e', $d)) !!}
-->
