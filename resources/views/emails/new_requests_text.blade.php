@php
    $requests = $requests ?? [];
    $listed = count($requests);
    $also = $also ?? [];
    $shown = $listed + count($also);
    $following = max(0, (int) ($newCount ?? 0) - $shown);
@endphp
@if (! $requests)
{{ __('messages.pending') }} {{ __('messages.requests') }}

{{ __('messages.hello') }},

{!! trans_choice('messages.new_requests_line', $requestCount, ['count' => $requestCount, 'name' => $role->name]) !!}
@else
{!! $shown === 1 ? __('messages.request_mail_eyebrow') : __('messages.request_mail_heading_many', ['count' => $shown]) !!} - {!! $role->name !!}
@foreach ($requests as $summary)

@if ($shown > 1)
{{ __('messages.request_mail_position', ['number' => $loop->iteration, 'count' => $shown]) }}
@endif
{!! $summary['title'] !!}
@if ($summary['when'])
{{ __('messages.date') }}: {!! $summary['when'] !!}
@endif
@if ($summary['from'])
{{ __('messages.request_mail_from') }}: {!! $summary['from'] !!}
@endif
@if ($summary['email'])
{{ __('messages.email') }}: {!! $summary['email'] !!}
@endif
@if ($summary['phone'])
{{ __('messages.phone') }}: {!! $summary['phone'] !!}
@endif
@if ($summary['where'])
{{ __('messages.venue') }}: {!! $summary['where'] !!}
@endif
@if ($summary['sub_schedule'])
{{ __('messages.subschedule') }}: {!! $summary['sub_schedule'] !!}
@endif
@foreach ($summary['answers'] as $answer)
{!! $answer['label'] !!}: {!! $answer['value'] !!}
@endforeach
@if ($summary['note'])
{{ __('messages.message') }}: {!! $summary['note'] !!}
@endif
@endforeach
@if ($also)

{{ __('messages.request_mail_also') }}
@foreach ($also as $line)
- {!! implode(' · ', array_filter([$line['title'], $line['from'], $line['day']], 'filled')) !!}
@endforeach
@endif
@if ($following > 0)

{!! trans_choice('messages.request_mail_more', $following, ['count' => $following]) !!}
@endif
@if ($requestCount > $shown + $following)

{!! __('messages.request_mail_waiting', ['count' => $requestCount]) !!}
@endif
@endif

{{ $requests ? __('messages.request_mail_button') : __('messages.view_details') }}: {{ $actionUrl }}

{{ __('messages.thank_you_for_using') }}

@if (empty($notificationEmailUnsubscribeUrl))
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
@include('emails.partials.notification_email_footer_text', ['scheduleName' => $role?->name])
