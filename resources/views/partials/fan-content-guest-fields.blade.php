{{--
    Name / email for fan content submitted without an account.

    Rendered only for signed-out visitors on schedules that accept guest submissions;
    everyone else either already has a user attached or is sent to sign in first.
    EventController::guestSubmitterAttributes() validates these server-side.

    The anti-spam token is a hidden field here rather than a widget per form: these
    forms repeat once per event part, and rendering a Turnstile widget in each would
    put dozens on a page. One shared widget (partials.fan-content-turnstile) fills
    every form's token on submit.
--}}
@php $fanGuestRole = $role ?? null; @endphp
@if ($fanGuestRole && ! auth()->check() && ! $fanGuestRole->fan_content_require_account)
<div class="flex flex-col sm:flex-row gap-2">
    <input type="text" name="guest_name" required maxlength="255"
           placeholder="{{ __('messages.name') }}" autocomplete="name"
           class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
    <input type="email" name="guest_email" required maxlength="255"
           placeholder="{{ __('messages.email') }}" autocomplete="email"
           class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
</div>
@if (\App\Utils\TurnstileUtils::isEnabled())
<input type="hidden" name="cf-turnstile-response" class="fan-content-turnstile-token">
@endif
@endif
