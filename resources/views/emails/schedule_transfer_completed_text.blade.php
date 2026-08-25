{{ $forPreviousOwner ? __('messages.schedule_transfer_sent_heading') : __('messages.schedule_transfer_received_heading') }}

{{ $forPreviousOwner
    ? __('messages.schedule_transfer_sent_intro', ['name' => $role?->name, 'email' => $transfer->to_email])
    : __('messages.schedule_transfer_received_intro', ['name' => $role?->name]) }}

{{ $role?->name }}
@if ($role)
{{ $role->getGuestUrl(true) }}
@endif
@if ($billingEnded)

{{ __('messages.schedule_transfer_sent_billing') }}
@elseif ($billingNoop)

{{ __('messages.schedule_transfer_sent_no_billing') }}
@endif
@if ($adminUrl)

{{ __('messages.schedule_transfer_open_schedule') }}: {{ $adminUrl }}
@if (config('app.hosted'))

{{ __('messages.schedule_transfer_received_billing') }}
@endif

{{ __('messages.schedule_transfer_received_payments') }}
@endif
