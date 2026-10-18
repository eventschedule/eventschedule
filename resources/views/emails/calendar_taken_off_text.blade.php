{{ trans_choice('messages.calendar_taken_off_title', $count, ['count' => number_format($count)]) }}

{{ __('messages.calendar_taken_off_body', ['provider' => $provider]) }}

@foreach ($names as $name)
- {{ $name }}
@endforeach

{{ __('messages.calendar_taken_off_mail_nothing_deleted') }}

{{ trans_choice('messages.calendar_see_events', $count, ['count' => number_format($count)]) }}: {{ $reviewUrl }}

{{ __('messages.thank_you_for_using') }}
