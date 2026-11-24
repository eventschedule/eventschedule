{{-- A schedule that sends through the platform's own mail, from somebody who has not verified a
     phone, may write to a small audience only. Said before they write the newsletter, not when
     Send refuses it. --}}
@if (config('app.hosted') && ! config('app.is_testing') && ! auth()->user()->isAdmin())
    @if (! $role->hasEmailSettings() && ! auth()->user()->hasVerifiedPhone())
    <x-page-notice tone="warn" class="news-notice" :title="__('messages.newsletter_verification_required_title', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)])">
        <p class="news-notice-body">{{ __('messages.newsletter_verify_short') }}</p>
        <x-slot:action>
            <span class="inline-flex flex-wrap gap-x-4 gap-y-1">
                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}#integration-tab-email" class="hover:underline">{{ __('messages.set_up_smtp') }} <span aria-hidden="true">&rarr;</span></a>
                <a href="{{ route('profile.edit') }}?highlight=phone#section-profile" class="hover:underline">{{ __('messages.verify_phone') }} <span aria-hidden="true">&rarr;</span></a>
            </span>
        </x-slot:action>
    </x-page-notice>
    @endif
@endif
