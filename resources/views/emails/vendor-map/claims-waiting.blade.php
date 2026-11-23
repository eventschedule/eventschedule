{{-- To a market's editors: the vendors who asked for a spot, and the vendors who said they are
     not coming, since the last such mail (VendorClaimNotifier::tellOwners()). One line each,
     worded here in the reader's language. Each part of a line is its own bidi isolate (U+2068,
     U+2069), so a Hebrew vendor's name beside an English "Stall 14" keeps its order. No phone
     number: those are for the editors' screens only. --}}
@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $toLine = function (array $l) use ($kind) {
        $what = filled($l['sells'] ?? null)
            ? $l['sells']
            : implode(', ', array_map(fn ($c) => __('messages.vm_cat_'.$c), $l['categories'] ?? []));
        $dates = implode(', ', array_map(fn ($d) => str_replace(' ', "\u{00A0}", \App\Services\VendorMaps\VendorMapPresenter::shortDate($d)), $l['dates'] ?? []));

        return implode(' · ', array_map(
            fn ($part) => "\u{2068}".$part."\u{2069}",
            array_filter([$l['name'], \App\Services\VendorMaps\VendorMapKinds::say($kind, 'spot', ['number' => $l['spot']]), $what, $dates], 'filled')
        ));
    };
    $claimLines = array_map($toLine, $claims);
    $absentLines = array_map($toLine, $absences);
    $preheader = \Illuminate\Support\Str::limit(implode(' · ', array_column(array_merge($claims, $absences), 'name')), 140) ?: $mailSubject;
    $heading = $claims
        ? trans_choice('messages.vm_mail_owner_waiting_heading', count($claims), ['count' => count($claims)])
        : trans_choice('messages.vm_mail_owner_absent_heading', count($absences), ['count' => count($absences)]);
@endphp
<x-email.layout :theme="$theme" :title="$mailSubject" :preheader="$preheader">
<x-email.heading :subtitle="__('messages.vm_mail_owner_intro', ['name' => $role->name])">{{ $heading }}</x-email.heading>

@if ($claimLines)
<x-email.section :label="__('messages.vm_mail_owner_section_waiting')" />
<x-email.list :items="$claimLines" />
@endif

@if ($absentLines)
<x-email.section :label="__('messages.vm_mail_owner_section_absent')" />
<x-email.list :items="$absentLines" />
@endif

@if ($following > 0)
<x-email.text variant="muted">{{ trans_choice('messages.vm_mail_owner_more', $following, ['count' => $following]) }}</x-email.text>
@endif

<x-email.button :href="$actionUrl">{{ __('messages.vm_mail_owner_button') }}</x-email.button>

<x-slot:footer>
@if (empty($notificationEmailUnsubscribeUrl))
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
@endif
@include('emails.partials.notification_email_footer', ['scheduleName' => $role->name])
</x-slot:footer>
</x-email.layout>
