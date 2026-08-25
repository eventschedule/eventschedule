{{ __('messages.schedule_transfer_declined_heading') }}

{{ __('messages.schedule_transfer_declined_intro', ['email' => $transfer->to_email, 'name' => $role?->name]) }}

{{ __('messages.schedule_transfer_declined_nothing_changed') }}

{{ __('messages.schedule_transfer_open_team') }}: {{ $teamUrl }}
