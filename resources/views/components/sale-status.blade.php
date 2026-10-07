@props(['status'])

{{-- What became of an order, as the kit's status mark (a dot and a word): paid is green, unpaid
     is amber, cancelled is red, and anything else (refunded, expired) is quiet. Five pages drew
     this pill five ways, each with its own icon. --}}
@php
    $tone = ['paid' => 'is-on', 'unpaid' => 'is-warn', 'cancelled' => 'is-bad'][$status] ?? '';
@endphp
<span {{ $attributes->merge(['class' => trim('event-status '.$tone)]) }}>{{ __('messages.'.$status) }}</span>
