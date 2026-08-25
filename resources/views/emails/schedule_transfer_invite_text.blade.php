{{ __('messages.schedule_transfer_invite_heading') }}

{{ __('messages.schedule_transfer_invite_intro', ['user' => $fromUser?->name, 'name' => $role?->name]) }}

{{ $role?->name }}
@if ($role)
{{ $role->getGuestUrl(true) }}
@endif

{{ __('messages.schedule_transfer_invite_what_moves') }}

{{ __('messages.schedule_transfer_review') }}: {{ $acceptUrl }}

{{ __('messages.schedule_transfer_invite_sign_in', ['email' => $transfer->to_email]) }}

{{ __('messages.schedule_transfer_invite_expires', ['date' => $transfer->expires_at?->format('M j, Y')]) }}

{{ __('messages.schedule_transfer_invite_ignore') }}
