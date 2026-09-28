{{ __('messages.ticket_trial_ending_subject', ['schedule' => $role->name, 'date' => $endDate]) }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __('messages.ticket_trial_ending_body', ['schedule' => $role->name, 'date' => $endDate]) }}

{{ __('messages.ticket_trial_ending_buyers') }}

{{ __('messages.upgrade_to_pro_plan') }}: {{ $planUrl }}
