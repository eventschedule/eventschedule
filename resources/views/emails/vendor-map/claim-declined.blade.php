{{-- To a vendor page's owners: the organizer has no place for them this time
     (VendorClaimNotifier). Short and kind, with the organizer's note when there is one. --}}
@php
    $theme = \App\Utils\EmailTheme::owner($vendor);
    $intro = __('messages.vm_mail_declined_intro', ['name' => $market->name, 'spot' => $spotWord]);
@endphp
<x-email.layout :theme="$theme" :title="$mailSubject" :preheader="$intro">
<x-email.heading>{{ __('messages.vm_mail_declined_heading') }}</x-email.heading>

<x-email.text>{{-- By the stall's own name: a vendor's account is often named after the stall, so its first word read "Hi Vegetables". --}}
{{ __('messages.hello') }} <bdi>{{ $vendor->translatedName() }}</bdi>,</x-email.text>

<x-email.text>{{ $intro }}</x-email.text>

@if ($note)
<x-email.quote :text="$note" :label="__('messages.vm_mail_declined_note')" />
@endif

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]">{{ __('messages.vm_mail_vendor_why', ['name' => $market->name]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
