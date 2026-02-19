@php
    $links = $block['data']['links'] ?? [];
    $iconBaseUrl = 'https://cdn.eventschedule.com/img/social';
    $template = $template ?? 'modern';
@endphp
@if (count($links))
<tr>
    <td align="center" style="padding: 20px 30px;">
        <table role="presentation" cellpadding="0" cellspacing="0">
            <tr>
                @foreach ($links as $link)
                @if (!empty($link['url']) && !empty($link['platform']))
                @php $label = ucfirst($link['platform']); $initial = $link['platform'] === 'website' ? '🔗' : strtoupper(substr($link['platform'], 0, 1)); @endphp
                @if ($template === 'classic')
                <td style="padding: 0 8px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 4px;">
                            <tr>
                                <td style="width: 32px; height: 32px; text-align: center; vertical-align: middle; color: #ffffff; font-size: 14px; font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ $initial }}</td>
                            </tr>
                        </table>
                    </a>
                </td>
                @elseif ($template === 'minimal')
                <td style="padding: 0 10px;">
                    <a href="{{ $link['url'] }}" style="color: {{ $style['accentColor'] }}; text-decoration: none; font-size: 13px; font-family: '{{ $style['fontFamily'] }}', sans-serif; text-transform: uppercase; letter-spacing: 1px;">{{ $label }}</a>
                </td>
                @elseif ($template === 'bold')
                <td style="padding: 0 8px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 50%;">
                            <tr>
                                <td style="width: 40px; height: 40px; text-align: center; vertical-align: middle; color: #ffffff; font-size: 18px; font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ $initial }}</td>
                            </tr>
                        </table>
                    </a>
                </td>
                @elseif ($template === 'compact')
                <td style="padding: 0 5px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 50%;">
                            <tr>
                                <td style="width: 24px; height: 24px; text-align: center; vertical-align: middle; color: #ffffff; font-size: 11px; font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ $initial }}</td>
                            </tr>
                        </table>
                    </a>
                </td>
                @else
                {{-- Modern (default) --}}
                <td style="padding: 0 8px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 50%;">
                            <tr>
                                <td style="width: 32px; height: 32px; text-align: center; vertical-align: middle; color: #ffffff; font-size: 14px; font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ $initial }}</td>
                            </tr>
                        </table>
                    </a>
                </td>
                @endif
                @endif
                @endforeach
            </tr>
        </table>
    </td>
</tr>
@endif
