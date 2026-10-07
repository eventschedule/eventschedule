{{-- A schedule that sends through the platform's own mail, from somebody who has not verified a
     phone, may write to a small audience only. Said before they write the newsletter, not when
     Send refuses it. --}}
@if (config('app.hosted') && ! config('app.is_testing') && ! auth()->user()->isAdmin())
    @if (! $role->hasEmailSettings() && ! auth()->user()->hasVerifiedPhone())
    <x-page-notice tone="warn" class="news-notice" :title="__('messages.newsletter_verification_required_title', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)])">
        <p class="news-notice-body">
            {!! __('messages.newsletter_verification_required_body', [
                'smtp_link' => route('role.edit', ['subdomain' => $role->subdomain]) . '#integration-tab-email',
                'phone_link' => route('profile.edit') . '?highlight=phone#section-profile',
                'limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50),
            ]) !!}
        </p>
    </x-page-notice>
    @endif
@endif
