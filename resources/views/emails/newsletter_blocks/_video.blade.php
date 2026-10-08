{{--
    A video, as its thumbnail with a play mark on it. The mark is part of the picture
    (AppController::youtubeThumbnail() draws it for play=1), because nothing layered over an image
    survives Outlook, and a bare thumbnail read as one more photo.
--}}
@php
    $videoId = $block['data']['videoId'] ?? '';
    $thumbnailUrl = $block['data']['thumbnailUrl'] ?? '';
    $videoUrl = $block['data']['url'] ?? '';
    $w = $nl->inner;
@endphp
@if ($videoId)
<tr>
<td align="center" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px; text-align: center;">
<a href="{{ $videoUrl }}" target="_blank" rel="noopener" style="display: block; text-decoration: none;"><img src="{{ $thumbnailUrl }}{{ str_contains($thumbnailUrl, '?') ? '&' : '?' }}play=1" width="{{ $w }}" alt="{{ __('messages.play_video') }}" style="display: block; width: 100%; max-width: {{ $w }}px; height: auto; border: 0; border-radius: {{ $nl->cardRadius }}px;"></a>
</td>
</tr>
@endif
