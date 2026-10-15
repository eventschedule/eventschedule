{{ __('messages.appointment_host_released_subject', ['host' => $hostName, 'name' => $role->name]) }}

{{ __('messages.appointment_host_released_intro', ['host' => $hostName, 'name' => $role->name]) }}

@foreach ($typeNames as $typeName)
- {{ $typeName }}
@endforeach
@if ($futureBookings > 0)

{{ trans_choice('messages.appointment_host_released_bookings', $futureBookings, ['count' => $futureBookings, 'host' => $hostName]) }}
{{ __('messages.appointments_host_see_bookings') }}: {{ $bookingsUrl }}
@endif

{{ __('messages.appointments_choose_host') }}: {{ $typesUrl }}
