@php
    $videoId = $block['data']['videoId'] ?? '';
    $thumbnailUrl = $block['data']['thumbnailUrl'] ?? '';
    $videoUrl = $block['data']['url'] ?? '';
    $template = $template ?? 'modern';
    $borderRadius = $template === 'bold' || $template === 'modern' ? 'border-radius: 8px;' : '';
@endphp
@if ($videoId)
<tr>
    <td align="center" style="padding: 20px 30px;">
        <a href="{{ $videoUrl }}" style="display: block; text-decoration: none; max-width: 560px; margin: 0 auto;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 560px;">
                <tr>
                    <td align="center" style="{{ $borderRadius }}">
                        <img src="{{ $thumbnailUrl }}" alt="Video" width="560" style="width: 100%; max-width: 560px; height: auto; display: block; {{ $borderRadius }}" />
                    </td>
                </tr>
            </table>
        </a>
    </td>
</tr>
@endif
