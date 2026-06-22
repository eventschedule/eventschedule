{{--
    Shared Submit / Follow / Manage action cluster for the compact header.
    Expects: $role, $accentColor, $contrastColor, and optional $onDark (dark bar vs light card).
--}}
@php
    $onDark = $onDark ?? false;
    $hasSubmitButton = ($role->isCurator() || $role->isVenue() || $role->isTalent()) && $role->accept_requests;
    $primaryBtnClass = 'inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 '
        . ($onDark ? 'focus-visible:ring-white/70 focus-visible:ring-offset-[#16171b]' : 'focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900');
@endphp
@if (config('app.hosted') || config('app.is_testing'))
    @if ($hasSubmitButton)
    <a href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}" class="inline-flex items-center justify-center flex-shrink-0">
        <button type="button"
            style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
            class="{{ $primaryBtnClass }}">
            {{ $role->isTalent() ? $role->customLabel('request_to_book') : $role->customLabel('submit_event') }}
        </button>
    </a>
    @endif
    @if (! is_demo_mode() && (
        ($hasSubmitButton && auth()->user() && ! auth()->user()->isFollowing($role->subdomain) && ! auth()->user()->isConnected($role->subdomain)) ||
        (! $hasSubmitButton && (! auth()->user() || ! auth()->user()->isConnected($role->subdomain)))
    ))
    <button type="button"
        data-follow-trigger
        data-follow-url="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
        data-schedule-name="{{ $role->name }}"
        data-schedule-image="{{ $role->profile_image_url }}"
        data-accent-color="{{ $accentColor }}"
        data-contrast-color="{{ $contrastColor }}"
        style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
        class="{{ $primaryBtnClass }} flex-shrink-0">
        {{ $role->customLabel('follow') }}
    </button>
    @endif
    @if (auth()->user() && auth()->user()->isMember($role->subdomain))
    <a href="{{ app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)) }}" class="inline-flex items-center justify-center flex-shrink-0">
        <button type="button"
            style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
            class="{{ $primaryBtnClass }}">
            {{ __('messages.manage') }}
        </button>
    </a>
    @endif
@endif
