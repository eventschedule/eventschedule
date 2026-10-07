@if ($role->isCurator() && !empty($venueDuplicateGroupCount))
<div class="pb-4">
    <a href="{{ route('role.merge_venues', ['subdomain' => $role->subdomain]) }}"
       class="block bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm text-gray-800 dark:text-gray-200 flex-1">
                {{ str_replace(':count', $venueDuplicateGroupCount, __('messages.merge_venues_banner')) }}
            </div>
        </div>
    </a>
</div>
@endif

@if ($role->isCurator() && isset($timezoneMismatchEvents) && $timezoneMismatchEvents->isNotEmpty())
<div class="pb-4">
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3" v-pre>
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm text-gray-800 dark:text-gray-200 flex-1">
                <p>{{ str_replace([':count', ':timezone'], [$timezoneMismatchEvents->count(), $role->timezone], __('messages.timezone_mismatch_banner')) }}</p>
                <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                    @foreach($timezoneMismatchEvents->take(10) as $tzEvent)
                    <li>
                        <a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($tzEvent->id)]) }}"
                           class="font-medium text-amber-700 dark:text-amber-300 underline hover:no-underline">{{ $tzEvent->name }}</a>
                    </li>
                    @endforeach
                    @if($timezoneMismatchEvents->count() > 10)
                    <li class="text-gray-500 dark:text-gray-400">{{ str_replace(':count', $timezoneMismatchEvents->count() - 10, __('messages.and_n_more')) }}</li>
                    @endif
                </ul>
                <div class="mt-2 flex flex-wrap items-center gap-4">
                    <form method="POST" action="{{ route('role.timezone_warning_dismiss', ['subdomain' => $role->subdomain]) }}">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">{{ __('messages.dismiss') }}</button>
                    </form>
                    <form method="POST" action="{{ route('role.timezone_warning_fix_events', ['subdomain' => $role->subdomain]) }}"
                          data-confirm="{{ str_replace(':timezone', $role->timezone, __('messages.timezone_fix_events_confirm')) }}">
                        @csrf
                        {{-- Echo back the timezone the confirmation dialog named, so the action can
                             refuse if the schedule timezone changed since the banner rendered. --}}
                        <input type="hidden" name="timezone" value="{{ $role->timezone }}">
                        <button type="submit" class="text-xs font-medium text-amber-700 dark:text-amber-300 underline hover:no-underline">{{ str_replace(':timezone', $role->timezone, __('messages.timezone_fix_events_button')) }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Flashed by EventController::store() for someone's first event: where it lives and what to
     do next, instead of a three-second toast. A draft has no public page until it is published,
     so it gets no link. The name is the user's own text, hence x-user-text. --}}
@php $firstEventCreated = session('first_event_created'); $eventCreated = session('event_created'); @endphp
{{-- Someone with a setup guide gets its "Your page" panel here instead, after every event save
     until their page has a few events on it (App\Utils\SetupGuide::surface()). One panel, one
     forward button. Everyone else keeps the panel below. --}}
@if (\App\Utils\SetupGuide::surface() === 'panel')
@include('partials.setup-guide', ['place' => 'panel'])
@elseif (is_array($firstEventCreated))
<div class="pb-4">
    <div class="ap-card rounded-xl p-6" role="status">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl shrink-0 {{ $firstEventCreated['is_draft'] ? 'bg-amber-50 dark:bg-amber-500/10' : 'bg-green-50 dark:bg-green-500/10' }}">
                @if ($firstEventCreated['is_draft'])
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                @else
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ $firstEventCreated['is_draft'] ? __('messages.first_event_draft_title') : __('messages.first_event_live_title') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <x-user-text class="font-medium text-gray-700 dark:text-gray-300">{{ $firstEventCreated['name'] }}</x-user-text>
                    &middot;
                    {{ $firstEventCreated['is_draft'] ? __('messages.first_event_draft_body') : __('messages.first_event_live_body') }}
                </p>
            </div>
        </div>

        @if (! empty($firstEventCreated['url']))
        <x-copy-link id="first-event-url" :value="$firstEventCreated['url']" class="mt-4" :label="__('messages.event_link')" />
        @endif

        <div class="mt-4 flex flex-wrap gap-3">
            {{-- Where it is comes first when the event was saved without it: it is the one thing a
                 guest cannot do without. #section-venue opens the Event tab on its location. --}}
            @if (empty($firstEventCreated['has_location']))
            <x-secondary-link href="{{ $firstEventCreated['edit_url'] }}#section-venue">
                {{ __('messages.add_location') }}
            </x-secondary-link>
            @endif
            <x-secondary-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}">
                {{ __('messages.add_another_event') }}
            </x-secondary-link>
            <x-secondary-link href="{{ $firstEventCreated['edit_url'] }}#section-tickets">
                {{ __('messages.add_tickets') }}
            </x-secondary-link>
            @if (! empty($firstEventCreated['url']))
            <x-brand-link href="{{ $firstEventCreated['url'] }}" target="_blank">
                {{ __('messages.view_event') }}
            </x-brand-link>
            @elseif (! empty($firstEventCreated['publish_url']))
            {{-- A draft can be published from here: "when you are ready" with no button was a
                 dead end on the page that had just said the event exists. --}}
            <x-secondary-link href="{{ $firstEventCreated['edit_url'] }}">
                {{ __('messages.edit_event') }}
            </x-secondary-link>
            <form method="POST" action="{{ $firstEventCreated['publish_url'] }}">
                @csrf
                <x-brand-button type="submit">{{ __('messages.publish') }}</x-brand-button>
            </form>
            @else
            <x-brand-link href="{{ $firstEventCreated['edit_url'] }}">
                {{ __('messages.edit_event') }}
            </x-brand-link>
            @endif
        </div>
    </div>
</div>
@elseif (is_array($eventCreated))
{{-- Every event after the first: one line with its link and whatever it was saved without. The
     name is its owner's text, so the sentence that carries it is v-pre. --}}
<div class="pb-4">
    <div class="ap-card rounded-xl px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2" role="status" id="event-created-strip">
        <div class="min-w-0 flex-1 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <svg class="w-5 h-5 shrink-0 {{ $eventCreated['is_draft'] ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="min-w-0 truncate" v-pre>{{ __($eventCreated['is_draft'] ? 'messages.event_created_as_draft' : 'messages.event_created_on_schedule', ['name' => $eventCreated['name']]) }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
            @if (empty($eventCreated['has_location']))
            <x-link href="{{ $eventCreated['edit_url'] }}#section-venue">{{ __('messages.add_location') }}</x-link>
            @endif
            @if (empty($eventCreated['has_tickets']))
            <x-link href="{{ $eventCreated['edit_url'] }}#section-tickets">{{ __('messages.add_tickets') }}</x-link>
            @endif
            @if (! empty($eventCreated['url']))
            <button type="button" id="event-created-copy" data-url="{{ $eventCreated['url'] }}"
                class="font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.copy_link') }}</button>
            <x-link href="{{ $eventCreated['url'] }}" target="_blank">{{ __('messages.view_event') }}</x-link>
            @else
            <x-link href="{{ $eventCreated['edit_url'] }}">{{ __('messages.edit_event') }}</x-link>
            @endif
        </div>
    </div>
</div>
@if (! empty($eventCreated['url']))
<script {!! nonce_attr() !!}>
// Delegated, like x-copy-link's: a listener on the button itself is lost if the page re-renders it.
document.addEventListener('click', function (e) {
    var button = e.target.closest ? e.target.closest('#event-created-copy') : null;
    if (! button || ! navigator.clipboard) { return; }
    navigator.clipboard.writeText(button.getAttribute('data-url')).then(function () {
        var original = button.textContent;
        button.textContent = @json(__('messages.copied'), JSON_UNESCAPED_UNICODE);
        setTimeout(function () { button.textContent = original; }, 2000);
    }).catch(function () {});
});
</script>
@endif
@endif

@include('role.partials.events-imported')

{{-- Flashed by EventController::update()/store() and RoleController::update() the first time a
     gallery goes from empty to published: the photos, and a way to see them as guests do. A draft
     or unlisted event (or a schedule with no public page) has nothing to open yet. --}}
@php $galleryPublished = session('gallery_published'); @endphp
@if (is_array($galleryPublished))
<div class="pb-4">
    <div class="ap-card rounded-xl p-6" role="status">
        <div class="flex items-start gap-3">
            <div class="dashboard-icon p-2 rounded-xl shrink-0 {{ $galleryPublished['is_hidden'] ? 'bg-amber-50 dark:bg-amber-500/10' : 'bg-green-50 dark:bg-green-500/10' }}" style="--icon-glow: {{ $galleryPublished['is_hidden'] ? 'rgba(245, 158, 11, 0.35)' : 'rgba(34, 197, 94, 0.35)' }}">
                <svg class="w-5 h-5 {{ $galleryPublished['is_hidden'] ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ $galleryPublished['is_hidden'] ? __('messages.gallery_saved_title') : __('messages.gallery_live_title') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($galleryPublished['is_hidden'])
                        {{ __('messages.gallery_saved_body') }}
                    @else
                        {{-- The event or schedule name is the owner's text, hence v-pre. --}}
                        <span v-pre>{{ trans_choice('messages.gallery_live_body', $galleryPublished['count'], ['count' => $galleryPublished['count'], 'name' => $galleryPublished['name']]) }}</span>
                    @endif
                </p>
            </div>
        </div>

        @if (! empty($galleryPublished['thumbs']))
        <div class="mt-4 flex gap-2">
            @foreach ($galleryPublished['thumbs'] as $thumb)
            <img src="{{ $thumb }}" alt="" class="h-16 w-16 rounded-lg object-cover bg-gray-100 dark:bg-gray-700" loading="lazy">
            @endforeach
        </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-3">
            <x-secondary-link href="{{ $galleryPublished['edit_url'] }}">
                {{ __('messages.edit_gallery') }}
            </x-secondary-link>
            @if (! empty($galleryPublished['url']))
            <x-brand-link href="{{ $galleryPublished['url'] }}" target="_blank">
                {{ __('messages.view_gallery') }}
            </x-brand-link>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Flashed by RoleController::update() when a venue map was just switched on, or is still on its
     first pass: the form's save lands here, so this is where the owner is told how far it is.
     Each venue's address is asked about once, a second apart (App\Services\PlaceLookupService),
     and the band is not on the guest page until all of them have been. The card asks
     role.venue_map.status until then and redraws itself; every wording it can show is printed
     here, so the script holds no text. --}}
@if (session('venue_map_saved') && \App\Services\VenueMap::enabledFor($role))
@php
    $venueMapNotice = \App\Services\VenueMap::status($role);
    $venueMapGuestUrl = $role->getGuestUrl() ? $role->getGuestUrl().'#gp-map' : null;
@endphp
<div class="pb-4">
    <div class="ap-card rounded-xl p-6" role="status" id="venue-map-notice"
         data-status-url="{{ route('role.venue_map.status', ['subdomain' => $role->subdomain]) }}"
         data-ready="{{ $venueMapNotice['ready'] ? '1' : '0' }}"
         data-total="{{ $venueMapNotice['total'] }}" data-asked="{{ $venueMapNotice['asked'] }}" data-placed="{{ $venueMapNotice['placed'] }}"
         data-title-preparing="{{ __('messages.venue_map_preparing') }}" data-body-preparing="{{ __('messages.venue_map_preparing_help') }}"
         data-title-live="{{ __('messages.venue_map_live') }}" data-body-live="{{ __('messages.venue_map_live_help') }}"
         data-title-short="{{ __('messages.venue_map_not_yet') }}" data-body-short="{{ __('messages.venue_map_needs_two') }}">
        <div class="flex items-start gap-3">
            <div class="dashboard-icon p-2 rounded-xl shrink-0 bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(78, 129, 250, 0.35)">
                <svg class="w-5 h-5 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100" data-map-notice-title></h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" data-map-notice-body></p>
                <div class="mt-3 flex items-center gap-3" data-map-notice-progress hidden>
                    <div class="h-1.5 flex-1 max-w-xs rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden" aria-hidden="true">
                        <div class="h-full rounded-full bg-[var(--brand-button-bg)] transition-all duration-200" data-map-notice-bar style="width: 0%"></div>
                    </div>
                    <span class="text-sm tabular-nums text-gray-700 dark:text-gray-300" dir="ltr" data-map-notice-count></span>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3" data-map-notice-actions hidden>
            <x-secondary-link href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}#engagement-tab-map">
                {{ __('messages.venue_map_review') }}
            </x-secondary-link>
            @if ($venueMapGuestUrl)
            <span data-map-notice-view hidden>
                <x-brand-link href="{{ $venueMapGuestUrl }}" target="_blank">
                    {{ __('messages.view_map') }}
                </x-brand-link>
            </span>
            @endif
        </div>
    </div>
</div>
<script {!! nonce_attr() !!}>
(function() {
    var card = document.getElementById('venue-map-notice');
    if (! card) { return; }

    var part = function(name) { return card.querySelector('[data-map-notice-' + name + ']'); };
    var tries = 0;

    function draw(state) {
        // Three things it can say: still asking, on the page, or asked and short of two pins.
        var kind = ! state.ready ? 'preparing' : (state.placed >= 2 ? 'live' : 'short');
        var body = kind === 'live' && state.placed >= state.total ? '' : card.getAttribute('data-body-' + kind);

        part('title').textContent = card.getAttribute('data-title-' + kind);
        part('body').textContent = body;
        part('body').hidden = ! body;
        part('progress').hidden = kind !== 'preparing' || ! state.total;
        part('bar').style.width = (state.total ? Math.round(100 * state.asked / state.total) : 0) + '%';
        part('count').textContent = state.asked + ' / ' + state.total;
        part('actions').hidden = kind === 'preparing';
        if (part('view')) { part('view').hidden = kind !== 'live'; }

        return kind !== 'preparing';
    }

    function ask() {
        // Five minutes of asking is longer than any first pass: after that the owner has the
        // list of venues to look at instead of a bar that does not move.
        if (++tries > 100) {
            part('actions').hidden = false;
            return;
        }

        fetch(card.getAttribute('data-status-url'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function(response) { return response.ok ? response.json() : null; })
            .then(function(state) {
                if (! state || ! draw(state)) { setTimeout(ask, 3000); }
            })
            .catch(function() { setTimeout(ask, 6000); });
    }

    var done = draw({
        ready: card.getAttribute('data-ready') === '1',
        total: parseInt(card.getAttribute('data-total'), 10) || 0,
        asked: parseInt(card.getAttribute('data-asked'), 10) || 0,
        placed: parseInt(card.getAttribute('data-placed'), 10) || 0,
    });

    if (! done) { setTimeout(ask, 3000); }
})();
</script>
@endif

{{-- Flashed by RoleController::update() when the owner picks a new event animation: the page
     looks its best right now, so invite them to share it while they are proud of it. --}}
@php $listAnimationSaved = session('list_animation_saved'); @endphp
@if (is_string($listAnimationSaved) && in_array($listAnimationSaved, \App\Models\Role::LIST_ANIMATIONS, true) && $listAnimationSaved !== 'none' && $role->getGuestUrl())
@php $listAnimationShareUrl = $role->getGuestUrl(true); @endphp
<div class="pb-4">
    <div class="ap-card rounded-xl p-6" role="status">
        <div class="flex items-start gap-3">
            <div class="dashboard-icon p-2 rounded-xl shrink-0 bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(78, 129, 250, 0.35)">
                <svg class="w-5 h-5 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.list_animation_share_title') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.list_animation_share_body', ['name' => __('messages.list_animation_'.$listAnimationSaved)]) }}
                </p>
            </div>
        </div>

        <x-copy-link id="list-animation-share-url" :value="$listAnimationShareUrl" class="mt-4" :label="__('messages.list_animation_share_link')" />

        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" id="list-animation-share-btn" data-share-url="{{ $listAnimationShareUrl }}" data-share-title="{{ $role->translatedName() }}"
                class="hidden ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="w-5 h-5 me-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
                {{ __('messages.list_animation_share') }}
            </button>
            <x-brand-link href="{{ $listAnimationShareUrl }}" target="_blank">
                {{ __('messages.view_schedule') }}
            </x-brand-link>
        </div>
    </div>
</div>
<script {!! nonce_attr() !!}>
(function () {
    var btn = document.getElementById('list-animation-share-btn');
    if (!btn || typeof navigator.share !== 'function') return;
    // A class, not the hidden attribute: inline-flex would outrank [hidden].
    btn.classList.remove('hidden');
    btn.addEventListener('click', function () {
        navigator.share({ title: btn.dataset.shareTitle, url: btn.dataset.shareUrl }).catch(function () {});
    });
})();
</script>
@endif

{{-- Below the two banners above on purpose: those warn about real problems, this is an
     optional invitation and must not outrank them. Held back on the first-event render so the
     panel above is not buried under an invitation on the one page load it exists for; both
     prompts are back on the next visit. --}}
@if(!empty($showFederationPrompt) && ! is_array($firstEventCreated))
    @include('partials.federation-prompt', ['padded' => true])
@endif

@if (! empty($federationListingSchedules) && $federationListingSchedules->isNotEmpty() && ! is_array($firstEventCreated))
    @include('partials.federation-listing-prompt', ['listingSchedules' => $federationListingSchedules, 'padded' => true])
@endif

@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'schedule'])

@if (count($unscheduled))
<div class="lg:flex lg:h-full lg:flex-col pt-5">
    <header class="flex items-center justify-between ps-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
            {{ __('messages.unscheduled') }}
        </h1>
    </header>
    <ul role="list" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pt-5">
        @foreach($unscheduled as $event)
        @if(! $event->starts_at)
        <li class="ap-card col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg text-center">
            <x-link href="{{ $event->role()->getGuestUrl() }}" target="_blank" class="block">
                <div class="flex flex-1 flex-col p-8">
                    @if ($event->role()->profile_image_url)
                    <img class="mx-auto rounded-lg h-32 w-32 flex-shrink-0 object-cover"
                        src="{{ $event->role()->profile_image_url }}"
                        alt="Profile Image">
                    @endif
                    <h3 class="mt-6 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->role()->name }}</h3>
                    <dl class="mt-1 flex flex-grow flex-col justify-between">
                        <dd class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3">{{ $event->role()->description }}</dd>
                    </dl>
                </div>
            </x-link>
            @if (!$isViewer)
            <div>
                <div class="-mt-px flex divide-x divide-gray-200 dark:divide-gray-700">
                    <div class="flex w-0 flex-1 cursor-pointer btn-navigate"
                        data-href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}">
                        <div
                            class="relative -me-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-es-lg border border-transparent py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.schedule') }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}"
                        class="form-confirm -ms-px flex w-0 flex-1"
                        data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="schedule">
                        <button type="submit" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-ee-lg border border-transparent py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                            </svg>
                            {{ __('messages.decline') }}
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </li>
        @endif
        @endforeach
    </ul>
</div>
@endif

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-navigate').forEach(function(el) {
        el.addEventListener('click', function() {
            location.href = this.getAttribute('data-href');
        });
    });

});
</script>
