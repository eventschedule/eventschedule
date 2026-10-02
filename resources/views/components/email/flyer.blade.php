{{--
    The event's own flyer across the top of the card (the layout's hero slot), when it is wide.

    Only the event's own upload: getImageUrl() falls back to the schedule's photo, which the sender
    row already shows. Only landscape (about 4:3 or wider): a portrait poster at full width would push
    everything the reader came for below the fold, so it is left out rather than shrunk. Hidden
    from Outlook for Windows, which cannot show the WebP derivative.
--}}
@props(['event'])
@php
    $url = $event->flyer_image_url ? $event->getImageUrl(960) : null;
    $size = $url ? $event->imageSourceDimensions() : null;
    $ratio = $size ? $size[0] / $size[1] : 0;
    $height = $ratio >= 1.3 ? (int) round(598 / $ratio) : 0;
@endphp
@if ($height > 0)
<!--[if !mso]><!-->
<img src="{{ $url }}" width="598" height="{{ $height }}" alt="{{ $event->name }}" style="display: block; width: 100%; max-width: 598px; height: auto; border: 0; border-radius: 15px 15px 0 0;">
<!--<![endif]-->
@endif
