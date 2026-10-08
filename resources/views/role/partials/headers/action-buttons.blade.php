{{--
    The header's buttons, drawn in one place for both header styles and the slim bar.

    Expects $role and $headActions (App\Utils\GuestHeader::actions(), worked out once by the
    header that includes this). $actionPart picks what is drawn:
      'main'  the one main button and, for a member, Manage (the banner, beside the logo)
      'rest'  whatever else a visitor may do (the banner's second row). On a phone Manage is
              here and not beside the logo, where three buttons do not fit: it is drawn in both
              places and the stylesheet shows one.
      'all'   everything in one row (the compact bar)
      'bar'   the main button alone, small (the slim bar that follows the banner down the page)

    One button is in the schedule's colour: Book a time where there is one, Follow otherwise.
    The forward action comes last. Follow stays a <button> (it opens a dialog, and
    GuestHeaderFollowTriggerTest counts it); the others are links.
--}}
@php
    $actionPart = $actionPart ?? 'all';
    $actionMain = $headActions['main'];
    $actionShowsMain = in_array($actionPart, ['main', 'all'], true);
    $actionShowsRest = in_array($actionPart, ['rest', 'all'], true);
    $actionShowsLead = $actionShowsMain || $actionPart === 'bar';
    $actionSize = $actionPart === 'bar' ? 'gk-btn-sm' : '';
    // Follow is the main button unless Book a time is; then it joins the rest.
    $actionFollowHere = $headActions['follow']
        && (($actionMain === 'follow' && $actionShowsLead) || ($actionMain !== 'follow' && $actionShowsRest));
@endphp
@if ($headActions['manage'] && $actionPart !== 'bar')
<a href="{{ app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)) }}" class="gk-btn {{ $actionPart === 'rest' ? 'gk-btn-secondary gk-head-manage-phone' : 'gk-btn-quiet gk-head-manage'.($actionPart === 'main' ? ' gk-head-manage-desk' : '') }}">
    <svg class="gk-head-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>
    <span>{{ __('messages.manage') }}</span>
</a>
@endif
@if ($actionShowsRest && $headActions['gift'])
<a href="{{ route('gift_card.purchase', ['subdomain' => $role->subdomain]) }}" class="gk-btn gk-btn-secondary">{{ __('messages.gift_cards') }}</a>
@endif
@if ($actionShowsRest && $headActions['submit'])
<a href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}" class="gk-btn gk-btn-secondary">{{ $role->isTalent() ? $role->customLabel('request_to_book') : $role->customLabel('submit_event') }}</a>
@endif
@if ($actionFollowHere)
<button type="button"
    data-follow-trigger
    data-follow-url="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
    data-subscribe-url="{{ route('role.audience.join', ['subdomain' => $role->subdomain]) }}"
    data-subscribe-label="{{ $role->customLabel('email_me_new_events') }}"
    data-account-note="{{ $role->willCreateAccountOnConfirm() ? '1' : '' }}"
    data-schedule-name="{{ $role->name }}"
    data-schedule-image="{{ $role->profile_image_url }}"
    data-accent-color="{{ $accentColor }}"
    data-contrast-color="{{ $contrastColor }}"
    class="gk-btn {{ $actionMain === 'follow' ? 'gk-btn-primary' : 'gk-btn-secondary' }} {{ $actionSize }} gk-head-follow">
    <svg class="gk-head-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1112 0c0 7 3 8 3 8H3s3-1 3-8z"/><path d="M10.3 20a1.95 1.95 0 003.4 0"/></svg>
    <span>{{ $role->customLabel('follow') }}</span>
</button>
@endif
@if ($actionPart === 'all' && $headActions['following'])
<span class="gk-head-following">
    <svg class="gk-head-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    {{ __('messages.following') }}
</span>
@endif
@if ($actionShowsLead && $headActions['book'])
<a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" class="gk-btn gk-btn-primary {{ $actionSize }}">{{ $role->customLabel('book_a_time') }}</a>
@endif
