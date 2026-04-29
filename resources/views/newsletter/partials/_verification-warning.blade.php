@if (config('app.hosted') && ! config('app.is_testing') && ! auth()->user()->isAdmin())
    @if (! $role->hasEmailSettings() && ! auth()->user()->hasVerifiedPhone())
    <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
        <div class="flex">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div class="ms-3 text-sm text-amber-700 dark:text-amber-300">
                <p class="font-medium">{{ __('messages.newsletter_verification_required_title') }}</p>
                <p class="mt-1">
                    {!! __('messages.newsletter_verification_required_body', [
                        'smtp_link' => route('role.edit', ['subdomain' => $role->subdomain]) . '?tab=email#section-integrations',
                        'phone_link' => route('profile.edit') . '?highlight=phone#section-profile',
                    ]) !!}
                </p>
            </div>
        </div>
    </div>
    @endif
@endif
