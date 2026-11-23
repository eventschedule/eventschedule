@php
    $toLine = function (array $l) use ($kind) {
        $what = filled($l['sells'] ?? null)
            ? $l['sells']
            : implode(', ', array_map(fn ($c) => __('messages.vm_cat_'.$c), $l['categories'] ?? []));
        $dates = implode(', ', array_map(fn ($d) => \App\Services\VendorMaps\VendorMapPresenter::shortDate($d), $l['dates'] ?? []));

        return implode(' · ', array_filter([$l['name'], \App\Services\VendorMaps\VendorMapKinds::say($kind, 'spot', ['number' => $l['spot']]), $what, $dates], 'filled'));
    };
@endphp
{!! $mailSubject !!}

{!! __('messages.vm_mail_owner_intro', ['name' => $role->name]) !!}
@if ($claims)

{!! __('messages.vm_mail_owner_section_waiting') !!}
@foreach ($claims as $line)
- {!! $toLine($line) !!}
@endforeach
@endif
@if ($absences)

{!! __('messages.vm_mail_owner_section_absent') !!}
@foreach ($absences as $line)
- {!! $toLine($line) !!}
@endforeach
@endif
@if ($following > 0)

{!! trans_choice('messages.vm_mail_owner_more', $following, ['count' => $following]) !!}
@endif

{{ __('messages.vm_mail_owner_button') }}: {!! $actionUrl !!}

{{ __('messages.thank_you_for_using') }}

@if (empty($notificationEmailUnsubscribeUrl))
{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}
@endif
@include('emails.partials.notification_email_footer_text', ['scheduleName' => $role->name])
