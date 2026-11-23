{!! $mailSubject !!}

{{ __('messages.hello') }} {!! $vendor->translatedName() !!},

{!! __('messages.vm_mail_declined_intro', ['name' => $market->name, 'spot' => $spotWord]) !!}
@if ($note)

{{ __('messages.vm_mail_declined_note') }}:
{!! $note !!}
@endif

{!! __('messages.vm_mail_vendor_why', ['name' => $market->name]) !!}
{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}
