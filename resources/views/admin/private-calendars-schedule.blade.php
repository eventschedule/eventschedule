<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'private-calendars'])

    {{-- One schedule a calendar import may have made into somebody's private calendar. The
         evidence first (what was found and where, read from the events as they are now), then
         the decision as four numbered steps in the order an operator takes them. Pause comes
         before Tell, so the mail can say it was done. Each step says what it will do, what it did
         and by whom, or why it cannot be taken; Hide and Pause can be undone here.

         Not a Vue mount: names and snippets are text. Snippets are escaped by
         CalendarPrivacy::highlight() before the match is marked. --}}
    @php
        $tz = auth()->user()->timezone ?: config('app.timezone');
        $when = fn ($moment) => $moment ? $moment->copy()->setTimezone($tz)->translatedFormat('M j, Y, H:i') : null;
        $by = fn ($moment, $user) => $user ? __('messages.private_calendars_done_by', ['date' => $when($moment), 'name' => $user->name ?: $user->email]) : $when($moment);
        $guestUrl = $role->getGuestUrl(true);
        $visibility = [
            'public' => __('messages.public'),
            'unlisted' => __('messages.unlisted'),
            'draft' => __('messages.draft'),
            'internal' => __('messages.internal'),
        ];
        $ownerLocale = $owner?->language_code && array_key_exists($owner->language_code, config('app.supported_languages')) ? $owner->language_code : 'en';
        $masked = $owner && str_contains((string) $owner->email, '@')
            ? mb_substr($owner->email, 0, 1).'•••'.substr($owner->email, strpos($owner->email, '@'))
            : null;
        $readingNow = collect($syncs)->reject(fn ($sync) => $sync['paused'])->pluck('label')->implode(', ');
        $button = 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
    @endphp

    <div class="page-shell">
        <div class="page-head">
            <div class="min-w-0">
                <a href="{{ route('admin.private_calendars') }}" class="page-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                    <span>@lang('messages.private_calendars')</span>
                </a>
                <h2 class="page-title"><bdi>{{ $role->name }}</bdi></h2>
                <p class="page-lead">
                    <a href="{{ $guestUrl }}" target="_blank" rel="noopener" class="event-link" dir="ltr">{{ $guestUrl }}</a>
                    <a href="{{ route('admin.schedules.edit', ['role' => $role->encodeId()]) }}" class="event-link">@lang('messages.private_calendars_schedule_admin')</a>
                </p>
            </div>
        </div>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'warning' => 'warn', 'error' => 'error']" />

            {{-- What was found, and where it came from. --}}
            <x-page-card beside :title="__('messages.private_calendars_found_title')" :lead="__('messages.private_calendars_found_lead')">
                <dl class="page-kv">
                    <div>
                        <dt>@lang('messages.private_calendars_col_found')</dt>
                        <dd>
                            @forelse ($kinds as $kind)
                            <span class="event-chip">{{ \App\Services\CalendarExposureWatch::kindName($kind) }}</span>
                            @empty
                            @lang('messages.none')
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt>@lang('messages.private_calendars_col_public')</dt>
                        <dd>{{ number_format($publicNow) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.calendar_sync')</dt>
                        <dd>
                            @forelse ($syncs as $sync)
                            <span class="privacy-sync">
                                {{ $sync['label'] }} <span class="c-sub">{{ $sync['paused'] ? __('messages.private_calendars_paused') : $sync['direction'] }}</span>
                                @if ($sync['main'])
                                <span class="event-chip">@lang('messages.private_calendars_main_calendar')</span>
                                @endif
                            </span>
                            @empty
                            @lang('messages.private_calendars_no_sync')
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt>@lang('messages.private_calendars_meeting_venues')</dt>
                        <dd>{{ number_format($stubCount) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.owner')</dt>
                        <dd>
                            @if ($owner)
                            <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($owner->id)]) }}" class="event-link"><bdi>{{ $owner->name ?: $owner->email }}</bdi></a>
                            @else
                            @lang('messages.none')
                            @endif
                        </dd>
                    </div>
                </dl>
                @if ($snippets)
                <div class="privacy-snippets">
                    @foreach ($snippets as $snippet)
                    <p class="privacy-match"><span class="c-sub">{{ \App\Services\CalendarExposureWatch::kindName($snippet['kind']) }}</span> <bdi>{{ $snippet['html'] }}</bdi></p>
                    @endforeach
                </div>
                @endif
            </x-page-card>

            {{-- The events, as they are now. --}}
            <x-page-card flush :title="__('messages.private_calendars_events_title')" :lead="__('messages.private_calendars_events_lead')">
                @if ($events->isNotEmpty())
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.event')</th>
                                <th scope="col">@lang('messages.date')</th>
                                <th scope="col">@lang('messages.visibility')</th>
                                <th scope="col">@lang('messages.private_calendars_col_found')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $item)
                            @php($event = $item['event'])
                            @php($state = $event->visibilityState())
                            <tr>
                                <td class="c-main c-wrap"><span class="c-strong"><bdi>{{ $event->name }}</bdi></span></td>
                                <td class="c-date" data-label="{{ __('messages.date') }}">{{ $event->starts_at ? $event->getStartDateTime(null, true)->translatedFormat('M j, Y, H:i') : '' }}</td>
                                <td data-label="{{ __('messages.visibility') }}">
                                    <span class="event-status {{ in_array($state, ['public', 'unlisted'], true) ? 'is-bad' : '' }}">{{ $visibility[$state] ?? $state }}</span>
                                </td>
                                <td class="c-wrap" data-label="{{ __('messages.private_calendars_col_found') }}">
                                    @foreach ($item['kinds'] as $kind)
                                    <span class="event-chip">{{ \App\Services\CalendarExposureWatch::kindName($kind) }}</span>
                                    @endforeach
                                </td>
                                <td class="c-actions">
                                    @if (! $event->is_draft)
                                    <a href="{{ $event->getGuestUrl($role->subdomain) }}" target="_blank" rel="noopener" class="event-link">@lang('messages.view')</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <x-page-empty compact :title="__('messages.private_calendars_no_events')" />
                @endif
            </x-page-card>

            {{-- The decision, in order. --}}
            <x-page-card flush :title="__('messages.private_calendars_steps_title')" :lead="__('messages.private_calendars_steps_lead')">
                <ol class="privacy-steps">
                    {{-- 1. Hide what is public now --}}
                    <li class="privacy-step">
                        <span class="privacy-step-number" aria-hidden="true">1</span>
                        <div class="privacy-step-body">
                            <h3 class="privacy-step-title">@lang('messages.private_calendars_step_hide')</h3>
                            @if ($review?->hidden_at)
                            <p class="privacy-step-done">{{ __('messages.private_calendars_hidden_state', ['events' => $review->hidden_count, 'venues' => $review->venue_count]) }} · {{ $by($review->hidden_at, $review->hider) }}</p>
                            @if ($review->kept_count > 0)
                            <p class="privacy-step-text">{{ trans_choice('messages.private_calendars_kept_with_tickets', $review->kept_count, ['count' => $review->kept_count]) }}</p>
                            @endif
                            @endif
                            @if ($publicCount > 0)
                            <p class="privacy-step-text">{{ trans_choice('messages.private_calendars_step_hide_text', $publicCount, ['count' => $publicCount]) }}@if ($stubCount > 0) {{ trans_choice('messages.private_calendars_step_hide_venues', $stubCount, ['count' => $stubCount]) }}@endif</p>
                            @elseif (! $review?->hidden_at)
                            <p class="privacy-step-text">@lang('messages.private_calendars_nothing_to_hide')</p>
                            @endif
                            <div class="privacy-step-actions">
                                @if ($heldByUs > 0)
                                <form method="POST" action="{{ route('admin.private_calendars.put_back', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link" data-confirm="{{ __('messages.private_calendars_put_back_confirm') }}">@lang('messages.private_calendars_put_back')</button>
                                </form>
                                @endif
                                @if ($publicCount > 0)
                                <form method="POST" action="{{ route('admin.private_calendars.hide', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="{{ $button }}">@lang('messages.private_calendars_hide')</button>
                                </form>
                                @endif
                            </div>
                            @if ($heldByUs > 0)
                            <p class="privacy-step-note">@lang('messages.private_calendars_put_back_note')</p>
                            @endif
                        </div>
                    </li>

                    {{-- 2. Pause the sync --}}
                    <li class="privacy-step">
                        <span class="privacy-step-number" aria-hidden="true">2</span>
                        <div class="privacy-step-body">
                            <h3 class="privacy-step-title">@lang('messages.private_calendars_step_pause')</h3>
                            @if ($paused && $review?->paused_at)
                            <p class="privacy-step-done">{{ __('messages.private_calendars_paused') }} · {{ $by($review->paused_at, $review->pauser) }}</p>
                            @endif
                            @if ($reading)
                            <p class="privacy-step-text">{{ __('messages.private_calendars_step_pause_text', ['calendar' => $readingNow]) }}</p>
                            @elseif ($paused)
                            <p class="privacy-step-text">@lang('messages.private_calendars_step_resume_text')</p>
                            @else
                            <p class="privacy-step-text">@lang('messages.private_calendars_not_reading')</p>
                            @endif
                            <div class="privacy-step-actions">
                                @if ($paused)
                                <form method="POST" action="{{ route('admin.private_calendars.resume', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.resume')</button>
                                </form>
                                @endif
                                @if ($reading)
                                <form method="POST" action="{{ route('admin.private_calendars.pause', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="{{ $button }}">@lang('messages.pause')</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </li>

                    {{-- 3. Tell the owner --}}
                    <li class="privacy-step">
                        <span class="privacy-step-number" aria-hidden="true">3</span>
                        <div class="privacy-step-body">
                            <h3 class="privacy-step-title">@lang('messages.private_calendars_step_email')</h3>
                            @if ($review?->emailed_at)
                            <p class="privacy-step-done">{{ __('messages.private_calendars_emailed_state') }} · {{ $by($review->emailed_at, $review->emailer) }}</p>
                            @endif
                            @if (! $canEmail)
                            <p class="privacy-step-text">@lang('messages.private_calendars_email_unavailable')</p>
                            @else
                            <p class="privacy-step-text">{{ __('messages.private_calendars_step_email_text', ['language' => __('messages.'.config('app.supported_languages')[$ownerLocale]), 'email' => $masked]) }}</p>
                            <div class="privacy-step-actions">
                                <a href="{{ route('admin.private_calendars.email_preview', ['hash' => $hash]) }}" target="_blank" rel="noopener" class="event-link">@lang('messages.preview')</a>
                                @if ($ownerLocale !== 'en')
                                <a href="{{ route('admin.private_calendars.email_preview', ['hash' => $hash, 'lang' => 'en']) }}" target="_blank" rel="noopener" class="event-link">@lang('messages.private_calendars_preview_english')</a>
                                @endif
                                @if ($emailAgainAt && $emailAgainAt->isFuture())
                                <span class="c-sub">{{ __('messages.private_calendars_email_again_from', ['date' => $emailAgainAt->copy()->setTimezone($tz)->translatedFormat('M j')]) }}</span>
                                @else
                                <form method="POST" action="{{ route('admin.private_calendars.email', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="{{ $button }}" data-confirm="{{ __('messages.private_calendars_send_confirm', ['email' => $masked]) }}">@lang('messages.send')</button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                    </li>

                    {{-- 4. Mark reviewed --}}
                    <li class="privacy-step">
                        <span class="privacy-step-number" aria-hidden="true">4</span>
                        <div class="privacy-step-body">
                            <h3 class="privacy-step-title">@lang('messages.private_calendars_mark_reviewed')</h3>
                            @if ($review?->reviewed_at)
                            <p class="privacy-step-done">{{ __('messages.private_calendars_reviewed_state') }} · {{ $by($review->reviewed_at, $review->reviewer) }}</p>
                            @endif
                            <p class="privacy-step-text">@lang('messages.private_calendars_step_reviewed_text')</p>
                            <div class="privacy-step-actions">
                                <form method="POST" action="{{ route('admin.private_calendars.reviewed', ['hash' => $hash]) }}">
                                    @csrf
                                    <x-brand-button type="submit">@lang('messages.private_calendars_mark_reviewed')</x-brand-button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ol>
            </x-page-card>
        </div>
    </div>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            .privacy-sync {
              display: block;
            }
            .privacy-sync + .privacy-sync {
              margin-top: 0.25rem;
            }
            .privacy-snippets {
              margin-top: 1rem;
            }
            .privacy-match {
              font-size: 0.875rem;
              line-height: 1.375rem;
              color: rgb(var(--ap-ink-2));
              overflow-wrap: anywhere;
            }
            .privacy-match + .privacy-match {
              margin-top: 0.5rem;
            }
            /* The four steps: a number in the kit's tint, then what the step does, what it did,
               and its buttons, the one that goes on last. */
            .privacy-steps {
              list-style: none;
              margin: 0;
              padding: 0;
            }
            .privacy-step {
              display: flex;
              gap: 1rem;
              padding: 1.25rem 1.5rem;
              border-top: 1px solid rgb(var(--ap-border));
            }
            .privacy-step-number {
              flex: none;
              display: inline-flex;
              align-items: center;
              justify-content: center;
              width: 2rem;
              height: 2rem;
              border-radius: 999px;
              background: var(--ap-tint-2);
              color: var(--brand-blue);
              font-weight: 700;
              font-size: 0.875rem;
            }
            .privacy-step-body {
              min-width: 0;
              flex: 1;
            }
            .privacy-step-title {
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .privacy-step-text,
            .privacy-step-note {
              margin-top: 0.25rem;
              font-size: 0.875rem;
              line-height: 1.375rem;
              color: rgb(var(--ap-ink-2));
              max-width: 110ch;
            }
            .privacy-step-note {
              color: rgb(var(--ap-ink-3));
            }
            .privacy-step-done {
              margin-top: 0.25rem;
              font-size: 0.875rem;
              font-weight: 500;
              color: rgb(var(--ap-ink));
            }
            .privacy-step-actions {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: flex-end;
              gap: 0.75rem 1rem;
              margin-top: 0.75rem;
            }
            .privacy-step-actions:empty {
              display: none;
            }
        </style>
    </x-slot>
</x-app-admin-layout>
