@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);
    // What the mail spells out (RequestSummary::describe() each). With none it is the count
    // alone, as it was before 2026-10.
    $requests = $requests ?? [];
    $listed = count($requests);
    // New requests the mail names in a line each (RequestSummary::line()), after the ones it
    // spells out: more arrived together than one mail has room to tell in full. Each part of a
    // line is its own bidi isolate (U+2068, U+2069), so that an English event name beside a
    // Hebrew sender and a date keeps the order it was written in. The day's spaces do not
    // break: a line that runs over wraps before the date, not between "24. Okt" and "2026".
    $also = collect($also ?? [])->map(fn ($line) => implode(' · ', array_map(
        fn ($part) => "\u{2068}".$part."\u{2069}",
        array_filter([$line['title'], $line['from'], str_replace(' ', "\u{00A0}", (string) $line['day'])], 'filled')
    )))->all();
    // Every request this mail names, which is what its heading and its subject count.
    $shown = $listed + count($also);
    // Told of by no mail yet and not in this one: the next mail has them.
    $following = max(0, (int) ($newCount ?? 0) - $shown);
    $title = $mailSubject ?? __('messages.new_requests_notification_subject', ['name' => $role->name, 'count' => $requestCount]);
    // Under the subject in the inbox. One request: who and when, which the subject has no room
    // for. Several: their names, since the subject names only the first.
    $preheader = match (true) {
        $listed === 0 => $title,
        $shown === 1 => implode(' · ', array_filter([$requests[0]['from'], $requests[0]['when']])) ?: $title,
        default => \Illuminate\Support\Str::limit(implode(' · ', array_slice(array_column($requests, 'title'), 1)), 140) ?: $title,
    };
    // Said under the heading, and only when something older is still waiting: "1 is waiting"
    // under one new request says nothing.
    $waiting = $listed && $requestCount > $shown + $following ? __('messages.request_mail_waiting', ['count' => $requestCount]) : null;
@endphp
<x-email.layout :theme="$theme" :title="$title" :preheader="$preheader">
@if ($listed === 0)
<x-email.heading>{{ __('messages.pending') }} {{ __('messages.requests') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>

<x-email.highlight :value="(string) $requestCount" :caption="trans_choice('messages.new_requests_caption', $requestCount, ['name' => $role->name])" />
@elseif ($shown === 1)
<x-email.heading :eyebrow="__('messages.request_mail_eyebrow')" :subtitle="$waiting" auto>{{ $requests[0]['title'] }}</x-email.heading>

@include('emails.partials.request_summary', ['summary' => $requests[0]])
@else
<x-email.heading :subtitle="$waiting">{{ __('messages.request_mail_heading_many', ['count' => $shown]) }}</x-email.heading>

{{-- The way to the list, before the list: on a phone the button at the foot of three requests
     is three screens down. --}}
<x-email.button :href="$actionUrl" variant="secondary" align="start">{{ __('messages.request_mail_button') }}</x-email.button>

{{-- Each request opens with its name, larger than anything in it, so that scrolling past finds
     where the next one starts. Its place is said in words: a bare "1 / 3" reads "3 / 1" in a
     right-to-left mail. --}}
@foreach ($requests as $summary)
<x-email.section :label="__('messages.request_mail_position', ['number' => $loop->iteration, 'count' => $shown])" />
<x-email.text><strong class="es-ink" dir="auto" style="font-size: 20px; line-height: 26px; font-weight: 700; color: #0f172a;">{{ $summary['title'] }}</strong></x-email.text>
@include('emails.partials.request_summary', ['summary' => $summary])
@endforeach

@if ($also)
<x-email.section :label="__('messages.request_mail_also')" />
<x-email.list :items="$also" />
@endif
@endif

@if ($following > 0)
<x-email.text variant="muted">{{ trans_choice('messages.request_mail_more', $following, ['count' => $following]) }}</x-email.text>
@endif

<x-email.button :href="$actionUrl">{{ $listed ? __('messages.request_mail_button') : __('messages.view_details') }}</x-email.button>

<x-slot:footer>
@if (empty($notificationEmailUnsubscribeUrl))
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
@endif
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
