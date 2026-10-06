@if(count($requests) == 0 || ! $role->email_verified_at)

<div class="ap-card rounded-xl page-empty">
    <svg class="page-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
    </svg>
    <h3>{{ __('messages.no_requests') }}</h3>
    <p>{{ __('messages.share_your_sign_up_link_to_get_more_requests') }}</p>
    <div class="page-actions">
        {{-- role.request is the public request page this copy is describing. The former
             'event.sign_up' name has no route, so this branch used to throw - and it is reachable,
             since the empty state also renders when the schedule's email is unverified. --}}
        <x-link href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}" target="_blank">
            {{ \App\Utils\UrlUtils::clean(route('role.request', ['subdomain' => $role->subdomain])) }}
        </x-link>
    </div>
</div>

@else

<div class="page-head">
    <p class="page-lead">{{ __('messages.requests_lead') }}</p>
    {{-- All at once is for a pile of them: beside one request it was a second Accept. --}}
    @if (! $isViewer && count($requests) > 1)
    <div class="page-actions">
        <form method="POST" action="{{ route('event.accept_all', ['subdomain' => $role->subdomain]) }}"
            data-confirm="{{ __('messages.accept_all_confirm', ['count' => count($requests)]) }}">
            @csrf
            <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.accept_all') }} ({{ count($requests) }})
            </button>
        </form>
    </div>
    @endif
</div>

<ul role="list" class="request-grid">
    @foreach($requests as $event)
    @php
        $bookingSale = $event->appointment_type_id ? $event->sales->first() : null;
        // Who asked. A booking-form request has no submitting schedule - bookingRequest()
        // re-attributes the row to this schedule's owner - so it is the person who filled the
        // form in (contact_name). Keyed on contact_name, not is_guest_submission: a SIGNED-IN
        // visitor's booking-form request is not a guest submission but still has nobody else's
        // schedule to show.
        $submitter = $event->appointment_type_id ? null : $event->contact_name;
        // Otherwise it is another schedule: the one that made the event, or failing that the
        // one this card always named (the act, for a venue or a curator; the venue, for an act).
        $asker = null;
        if (! $event->appointment_type_id && ! $submitter) {
            $asker = $event->creatorRole && $event->creatorRole->id !== $role->id
                ? $event->creatorRole
                : (($role->isVenue() || $role->isCurator()) ? $event->role() : $event->venue);
            if ($asker && $asker->id === $role->id) {
                $asker = null;
            }
        }
        $groupId = $event->getGroupIdForSubdomain($role->subdomain);
        $group = $groupId ? \App\Models\Group::find($groupId) : null;
        $requestFields = $event->appointment_type_id ? [] : $role->getRequestFormCustomFields();
        $requestValues = $event->appointment_type_id ? [] : $event->getCustomFieldValues();
    @endphp
    <li class="ap-card rounded-xl request-card">
        <div class="request-body">
            @if ($asker && $asker->profile_image_url)
            <img src="{{ $asker->profile_image_url }}" alt="">
            @endif
            <div class="request-text">
                @if ($event->appointment_type_id)
                    {{-- An appointment booking waiting to be confirmed. --}}
                    <p class="request-kind">
                        <span dir="auto" v-pre>{{ $event->appointmentType?->name ?? __('messages.appointments') }}</span>
                        {{-- A booking that came BACK to pending after a move renders identically to a
                             brand new request otherwise, and this list is ordered by the booking's
                             original created_at - so it can sit pages down and get swept up by
                             Accept all. --}}
                        @if ((int) $event->ical_sequence > 0)
                        <span class="event-chip">{{ __('messages.appointments_moved_badge') }}</span>
                        @endif
                    </p>
                    <h3 class="request-title" v-pre><bdi>{{ $bookingSale?->name }}</bdi></h3>
                    @if ($event->starts_at)
                    <p class="request-line">{{ $event->localStartsAt(true) }}</p>
                    @endif
                    @if ($bookingSale?->email)
                    <p class="request-line"><a href="mailto:{{ $bookingSale->email }}" class="event-link" dir="ltr">{{ $bookingSale->email }}</a></p>
                    @endif
                    @if ($bookingSale?->phone)
                    <p class="request-line"><a href="tel:{{ $bookingSale->phone }}" class="event-link" dir="ltr">{{ $bookingSale->phone }}</a></p>
                    @endif
                    @if ($bookingSale && $bookingSale->payment_amount > 0)
                    <p class="request-line is-quiet">{{ \App\Utils\MoneyUtils::format((float) $bookingSale->payment_amount, $event->ticket_currency_code) }} &middot; {{ $bookingSale->status === 'paid' ? __('messages.paid') : __('messages.unpaid') }}</p>
                    @endif
                    @if ($event->description)
                    {{-- The guest's own notes, captured at booking. --}}
                    <p class="request-note line-clamp-4" v-pre><bdi>{{ $event->description }}</bdi></p>
                    @endif
                @else
                    @if ($submitter)
                    <p class="request-kind">{{ __($role->isTalent() ? 'messages.booking_request' : 'messages.submit_event') }}</p>
                    @endif
                    {{-- What is being asked for, and by whom: a card used to name one or the other.
                         A venue saw the act's name twice over and never the event's; an act saw the
                         event's and not who wanted it. --}}
                    <h3 class="request-title" v-pre><bdi>{{ $event->translatedName() }}</bdi></h3>
                    @if ($submitter)
                    <p class="request-line">{{ __('messages.from') }} <span dir="auto" v-pre>{{ $submitter }}</span></p>
                    @elseif ($asker)
                    <p class="request-line">
                        {{ __('messages.from') }}
                        @if ($asker->getGuestUrl())
                        <x-link href="{{ $asker->getGuestUrl() }}" target="_blank"><span dir="auto" v-pre>{{ $asker->name }}</span></x-link>
                        @else
                        <span dir="auto" v-pre>{{ $asker->name }}</span>
                        @endif
                    </p>
                    @endif
                    @if ($event->starts_at)
                    <p class="request-line is-quiet">
                        {{ $event->localStartsAt(true) }}
                        @if ($group)
                        <span class="event-chip" v-pre>{{ $group->translatedName() }}</span>
                        @endif
                    </p>
                    @elseif ($group)
                    <p class="request-line is-quiet is-chip-only"><span class="event-chip" v-pre>{{ $group->translatedName() }}</span></p>
                    @endif
                    @if ($submitter && ($event->contact_email || $event->contact_phone))
                    <p class="request-line" data-request-contact>
                        @if ($event->contact_email)
                        <a href="mailto:{{ $event->contact_email }}" class="event-link" dir="ltr">{{ $event->contact_email }}</a>
                        @endif
                        @if ($event->contact_phone)
                        <a href="tel:{{ $event->contact_phone }}" class="event-link {{ $event->contact_email ? 'ms-3' : '' }}" dir="ltr">{{ $event->contact_phone }}</a>
                        @endif
                    </p>
                    @endif

                    {{-- Answers to the schedule's request-form questions. Owner-only surface, so
                         private fields are shown here too. --}}
                    @if ($requestFields && $requestValues)
                    <dl class="mt-2 space-y-2">
                        @foreach ($requestFields as $fieldKey => $field)
                            @php $answer = $requestValues[$fieldKey] ?? null; @endphp
                            @if ($answer !== null && $answer !== '')
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400" v-pre><bdi>{{ $role->customFieldLabel($field, $fieldKey) }}</bdi></dt>
                                <dd class="text-sm text-gray-700 dark:text-gray-300">
                                    @if (($field['type'] ?? '') === 'multiselect')
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach (array_filter(array_map('trim', explode(',', (string) $answer))) as $answerPart)
                                            <span class="inline-block bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-0.5 rounded-full" dir="auto" v-pre>{{ $answerPart }}</span>
                                            @endforeach
                                        </div>
                                    @elseif (($field['type'] ?? '') === 'switch')
                                        {{ $answer ? __('messages.yes') : __('messages.no') }}
                                    @else
                                        <span class="line-clamp-3" dir="auto" v-pre>{{ $answer }}</span>
                                    @endif
                                </dd>
                            </div>
                            @endif
                        @endforeach
                    </dl>
                    @endif

                    {{-- The guest's own message on a form request; on a request from a schedule,
                         a line about that schedule, so it can be placed without opening it. --}}
                    @if ($submitter && $event->description)
                    <p class="request-note line-clamp-4" v-pre><bdi>{{ $event->description }}</bdi></p>
                    @elseif ($asker && $asker->description_html)
                    {{-- From the rendered description: the stored one is Markdown, and its asterisks
                         and brackets would be shown as typed. --}}
                    <p class="request-line is-quiet line-clamp-2" v-pre><bdi>{{ \Illuminate\Support\Str::limit(\App\Utils\SeoUtils::plainText($asker->description_html), 160) }}</bdi></p>
                    @endif
                @endif
            </div>
        </div>
        <div class="request-foot">
            @if (! $event->appointment_type_id)
            <a href="{{ $event->getGuestUrl() }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
            @if (! $isViewer)
            <a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}" class="event-link">{{ __('messages.edit') }}</a>
            @endif
            @endif
            @if (! $isViewer)
            <div class="request-answer">
                <form method="POST" action="{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}"
                    data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="requests">
                    <button type="submit" class="event-link is-danger">{{ __('messages.decline') }}</button>
                </form>
                <form method="POST" action="{{ route('event.accept', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}">
                    @csrf
                    <x-brand-button type="submit" size="sm" class="test-accept-event">{{ __('messages.accept') }}</x-brand-button>
                </form>
            </div>
            @endif
        </div>
    </li>
    @endforeach
</ul>

@endif
