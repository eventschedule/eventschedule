{{-- What AudienceMailGuard says about sending from this schedule, said where the owner sends:
     email paused on the account (with the way to ask for it back), and imported addresses that
     wait for the first payment, and a trial's free-plan allowance with the way to start paying
     now (Role::newsletterLimit()). Hosted only, through the platform's own mail; the guard and
     the model decide each, so nothing here can disagree with what Send does. Needs $role. --}}
@php
    $mailGuardUser = auth()->user();
    // Read-only: a page never records a pause; a send attempt or Request access does.
    $mailGuardPaused = \App\Services\AudienceMailGuard::mailPausedForDisplay($role, $mailGuardUser);
    $mailGuardMine = $mailGuardPaused && \App\Services\AudienceMailGuard::wouldBePaused($mailGuardUser);
    $mailGuardImports = ($showImportRule ?? false) && \App\Services\AudienceMailGuard::applies($role) && ! $role->mayMailUnsolicitedAudience();
    $mailGuardTrial = $role->newsletterLimit() !== null && $role->onSubscriptionTrial();
@endphp

@if ($mailGuardPaused)
<x-page-notice tone="warn" class="news-notice" :title="__('messages.mail_paused_owner_title')">
    <p class="news-notice-body">
        @if ($mailGuardMine && $mailGuardUser->mail_access_requested_at && \App\Services\AudienceMailGuard::requestedRecently($mailGuardUser))
        {{ __('messages.mail_paused_owner_asked') }}
        @elseif ($mailGuardMine)
        {{ __('messages.mail_paused_owner_body') }}
        @else
        {{ __('messages.mail_paused_owner_team') }}
        @endif
    </p>
    @if ($mailGuardMine && ! \App\Services\AudienceMailGuard::requestedRecently($mailGuardUser))
    <x-slot name="action">
        <form method="POST" action="{{ route('newsletter.request_mail_access', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
            @csrf
            <button type="submit" class="event-link">{{ __('messages.mail_access_request') }}</button>
        </form>
    </x-slot>
    @endif
</x-page-notice>
@endif

@if ($mailGuardImports)
<x-page-notice tone="info" class="news-notice">
    {{ __('messages.newsletter_imports_wait_for_payment') }}
    <x-slot name="action">
        <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}#integration-tab-email" class="event-link">{{ __('messages.newsletter_use_own_email') }}</a>
    </x-slot>
</x-page-notice>
@endif

@if ($mailGuardTrial)
<x-page-notice tone="info" class="news-notice">
    {{ __('messages.newsletter_trial_limit', ['limit' => number_format($role->newsletterLimit()), 'plan_limit' => number_format($role->planNewsletterLimit())]) }}
    @if ((int) $role->user_id === (int) $mailGuardUser->id && $role->canEndTrial())
    <x-slot name="action">
        <form method="POST" action="{{ route('subscription.end_trial', ['subdomain' => $role->subdomain]) }}" data-confirm="{{ __('messages.end_trial_confirm') }}">
            @csrf
            <button type="submit" class="event-link">{{ __('messages.end_trial_now') }}</button>
        </form>
    </x-slot>
    @endif
</x-page-notice>
@endif
