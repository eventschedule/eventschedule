@php
    $links = $block['data']['links'] ?? [];
    $template = $template ?? 'modern';
@endphp
@if (count($links))
<tr>
    <td align="center" style="padding: 20px 30px;">
        <table role="presentation" cellpadding="0" cellspacing="0">
            <tr>
                @foreach ($links as $link)
                @if (!empty($link['url']) && !empty($link['platform']))
                @php
                    $platform = $link['platform'];
                    $label = ucfirst($platform);
                    $iconUrl = url('/images/social-icons/' . $platform . '.png');
                @endphp
                @if ($template === 'classic')
                <td style="padding: 0 8px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 4px;">
                            <tr>
                                <td style="width: 32px; height: 32px; text-align: center; vertical-align: middle;">
                                    <img src="{{ $iconUrl }}" alt="{{ $label }}" width="16" height="16" style="display: block; margin: 0 auto;" />
                                </td>
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
                                <td style="width: 40px; height: 40px; text-align: center; vertical-align: middle;">
                                    <img src="{{ $iconUrl }}" alt="{{ $label }}" width="20" height="20" style="display: block; margin: 0 auto;" />
                                </td>
                            </tr>
                        </table>
                    </a>
                </td>
                @elseif ($template === 'compact')
                <td style="padding: 0 5px;">
                    <a href="{{ $link['url'] }}" style="text-decoration: none;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}; border-radius: 50%;">
                            <tr>
                                <td style="width: 24px; height: 24px; text-align: center; vertical-align: middle;">
                                    <img src="{{ $iconUrl }}" alt="{{ $label }}" width="14" height="14" style="display: block; margin: 0 auto;" />
                                </td>
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
                                <td style="width: 32px; height: 32px; text-align: center; vertical-align: middle;">
                                    <img src="{{ $iconUrl }}" alt="{{ $label }}" width="16" height="16" style="display: block; margin: 0 auto;" />
                                </td>
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
