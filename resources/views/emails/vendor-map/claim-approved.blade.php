{{-- To a vendor page's owners: the organizer has a place for them (VendorClaimNotifier). The
     owner voice on the VENDOR's page, the market named as a schedule, in the market's language.
     The map is embedded (CID), never a remote image: it is drawn for this mail alone. --}}
@php
    $theme = \App\Utils\EmailTheme::owner($vendor);
    $intro = __('messages.vm_mail_approved_intro', ['name' => $market->name]);
    $size = $stallPng ? @getimagesizefromstring($stallPng) : false;
    $imgW = $size && $size[0] > 0 ? min(552, (int) $size[0]) : 0;
    $imgH = $imgW > 0 ? (int) round($imgW * $size[1] / $size[0]) : 0;
    $when = $everyDate
        ? \App\Services\VendorMaps\VendorMapKinds::say($kind, 'every_date')
        : implode(', ', $dateLabels);
@endphp
<x-email.layout :theme="$theme" :title="$mailSubject" :preheader="$intro">
<x-email.heading :eyebrow="__('messages.vm_mail_approved_eyebrow')" tone="success" auto>{{ $spotWord }}</x-email.heading>

<x-email.text>{{-- By the stall's own name: a vendor's account is often named after the stall, so its first word read "Hi Vegetables". --}}
{{ __('messages.hello') }} <bdi>{{ $vendor->translatedName() }}</bdi>,</x-email.text>

<x-email.text>{{ $intro }}</x-email.text>

@if ($imgH > 0)
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center" style="padding: 0 0 24px; text-align: center;">
<img src="{{ $message->embedData($stallPng, 'stall-map.png', 'image/png') }}" width="{{ $imgW }}" height="{{ $imgH }}" alt="{{ __('messages.vm_mail_map_alt') }}" style="display: block; margin: 0 auto; width: 100%; max-width: {{ $imgW }}px; height: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
</td>
</tr>
</table>
@endif

<x-email.details panel>
<x-email.item :label="__('messages.vm_mail_where_label')">{{ $spotWord }}</x-email.item>
@if ($nextLabel)
<x-email.item :label="__('messages.vm_mail_next_label')">{{ $nextLabel }}</x-email.item>
@endif
<x-email.item :label="__('messages.vm_mail_dates_label')" wide>{{ $when }}</x-email.item>
</x-email.details>

@if ($pageUrl)
<x-email.button :href="$pageUrl">{{ __('messages.vm_mail_your_page') }}</x-email.button>
@endif

<x-email.text align="center"><x-email.link :href="$shareUrl">{{ __('messages.vm_mail_share') }}</x-email.link>@if ($calendarUrl)&nbsp;&nbsp;&middot;&nbsp;&nbsp;<x-email.link :href="$calendarUrl">{{ __('messages.add_to_calendar') }}</x-email.link>@endif</x-email.text>

@if ($notComingUrl)
<x-email.section :label="__('messages.vm_mail_not_coming_title')" />
<x-email.text>{{ __('messages.vm_mail_not_coming_body') }}</x-email.text>
<x-email.button :href="$notComingUrl" variant="secondary" align="start">{{ __('messages.vm_mail_not_coming_button') }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]">{{ __('messages.vm_mail_vendor_why', ['name' => $market->name]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
