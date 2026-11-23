@php
    $when = $everyDate
        ? \App\Services\VendorMaps\VendorMapKinds::say($kind, 'every_date')
        : implode(', ', $dateLabels);
@endphp
{!! $mailSubject !!}

{{ __('messages.hello') }} {!! $vendor->translatedName() !!},

{!! __('messages.vm_mail_approved_intro', ['name' => $market->name]) !!}

{{ __('messages.vm_mail_where_label') }}: {!! $spotWord !!}
@if ($nextLabel)
{{ __('messages.vm_mail_next_label') }}: {!! $nextLabel !!}
@endif
{{ __('messages.vm_mail_dates_label') }}: {!! $when !!}

@if ($pageUrl)
{{ __('messages.vm_mail_your_page') }}: {!! $pageUrl !!}
@endif
{{ __('messages.vm_mail_share') }}: {!! $shareUrl !!}
@if ($calendarUrl)
{{ __('messages.add_to_calendar') }}: {!! $calendarUrl !!}
@endif

@if ($notComingUrl)
{{ __('messages.vm_mail_not_coming_title') }}
{{ __('messages.vm_mail_not_coming_body') }}
{{ __('messages.vm_mail_not_coming_button') }}: {!! $notComingUrl !!}{{-- Raw: plain text has no entity decoding. --}}
@endif

{!! __('messages.vm_mail_vendor_why', ['name' => $market->name]) !!}
{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}
