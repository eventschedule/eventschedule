@php
    $links = $block['data']['links'] ?? [];
    $iconBaseUrl = 'https://cdn.eventschedule.com/img/social';
@endphp
@if (count($links))
<tr>
    <td align="center" style="padding: 20px 30px;">
        <table role="presentation" cellpadding="0" cellspacing="0">
            <tr>
                @foreach ($links as $link)
                @if (!empty($link['url']) && !empty($link['platform']))
                <td style="padding: 0 8px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        @php
                            $label = ucfirst($link['platform']);
                        @endphp
                        <span style="display: inline-block; width: 32px; height: 32px; background-color: {{ $style['accentColor'] }}; border-radius: 50%; text-align: center; line-height: 32px; color: #ffffff; font-size: 14px; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ strtoupper(substr($link['platform'], 0, 1)) }}</span>
                    </a>
                </td>
                @endif
                @endforeach
            </tr>
        </table>
    </td>
</tr>
@endif
