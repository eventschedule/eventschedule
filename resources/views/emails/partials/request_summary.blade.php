{{-- One request, as the mail that announces it prints it: the facts in a grid, then the sender's
     own message. $summary is RequestSummary::describe(). Every value is somebody's typing, so it
     is printed as text and never as a link: a mail client makes an address pressable by itself.
     The answers run the full width, one under the other, in the owner's own order. A multi-line
     answer is somebody's prose, like the message under it, and is set in the same plain weight:
     six hundred characters of bold is a wall. --}}
<x-email.details panel>
{{-- The hours are their own left-to-right run: in a Hebrew or Arabic mail "8:00 PM - 10:00 PM"
     otherwise reads "PM - 10:00 PM 8:00" (emails/partials/appointment_datetime has the rule). --}}
@if ($summary['day'])
<x-email.item :label="__('messages.date')" wide>{{ $summary['day'] }}@if ($summary['time']) &middot; <bdi dir="ltr">{{ $summary['time'] }}</bdi>@endif</x-email.item>
@endif
@if ($summary['from'])
<x-email.item :label="__('messages.request_mail_from')" :wide="! $summary['phone']"><bdi>{{ $summary['from'] }}</bdi></x-email.item>
@endif
@if ($summary['phone'])
<x-email.item :label="__('messages.phone')" ltr>{{ $summary['phone'] }}</x-email.item>
@endif
@if ($summary['email'])
<x-email.item :label="__('messages.email')" wide ltr>{{ $summary['email'] }}</x-email.item>
@endif
@if ($summary['where'])
<x-email.item :label="__('messages.venue')" wide><bdi>{{ $summary['where'] }}</bdi></x-email.item>
@endif
@if ($summary['sub_schedule'])
<x-email.item :label="__('messages.subschedule')" wide><bdi>{{ $summary['sub_schedule'] }}</bdi></x-email.item>
@endif
@foreach ($summary['answers'] as $answer)
@if ($answer['type'] === 'multiline_string')
<x-email.item :label="$answer['label']" wide><span dir="auto" style="font-weight: 400; line-height: 26px;">{!! nl2br(e($answer['value'])) !!}</span></x-email.item>
@else
<x-email.item :label="$answer['label']" wide><bdi>{{ $answer['value'] }}</bdi></x-email.item>
@endif
@endforeach
</x-email.details>
@if ($summary['note'])
<x-email.quote :text="$summary['note']" :label="__('messages.message')" />
@endif
