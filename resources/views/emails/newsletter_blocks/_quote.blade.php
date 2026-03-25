@php
    $text = $block['data']['text'] ?? '';
    $author = $block['data']['author'] ?? '';
    $title = $block['data']['title'] ?? '';
    $template = $template ?? 'modern';
@endphp
@if ($text)
<tr>
    <td style="padding: 20px 30px;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="{{ $startBorder ?? 'border-left' }}: 4px solid {{ $style['accentColor'] }}; {{ $startPadding ?? 'padding-left' }}: 20px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 28px; line-height: 1; color: {{ $style['accentColor'] }}; padding-bottom: 4px;">
                                &ldquo;
                            </td>
                        </tr>
                        <tr>
                            <td style="font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 17px; line-height: 1.6; color: {{ $style['textColor'] }}; font-style: italic;">
                                {{ $text }}
                            </td>
                        </tr>
                        @if ($author)
                        <tr>
                            <td style="padding-top: 12px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 14px; color: {{ $style['textColor'] }};">
                                <strong>{{ $author }}</strong>@if ($title)<span style="color: {{ $style['textColor'] }}cc;">,&nbsp;{{ $title }}</span>@endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endif
