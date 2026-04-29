@php
    $sponsors = $block['data']['resolvedSponsors'] ?? [];
    $sponsorTitle = $block['data']['sponsorTitle'] ?? '';
    $template = $template ?? 'modern';
    $sponsorCount = count($sponsors);
    $cols = min($sponsorCount, 4);
    $colWidth = $cols > 0 ? floor(100 / $cols) : 100;
@endphp
@if (!empty($sponsors))
<tr>
    <td style="padding: 20px 30px;">
        @if ($sponsorTitle)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding-bottom: 16px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 18px; font-weight: bold; color: {{ $style['textColor'] }};">
                    {{ $sponsorTitle }}
                </td>
            </tr>
        </table>
        @endif
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @foreach ($sponsors as $index => $sponsor)
                <td align="center" valign="top" style="padding: 8px; width: {{ $colWidth }}%;">
                    <table role="presentation" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" style="padding-bottom: 4px;">
                                @if (!empty($sponsor['url']))
                                <a href="{{ $sponsor['url'] }}" style="text-decoration: none;">
                                @endif
                                @if (!empty($sponsor['logo_url']))
                                <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsor['display_name'] ?? '' }}" width="120" style="max-width: 120px; max-height: 80px; height: auto; display: block;" />
                                @endif
                                @if (!empty($sponsor['url']))
                                </a>
                                @endif
                            </td>
                        </tr>
                        @if (!empty($sponsor['display_name']))
                        <tr>
                            <td align="center" style="font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 12px; color: {{ $style['textColor'] }}; padding-top: 4px;">
                                @if (!empty($sponsor['url']))
                                <a href="{{ $sponsor['url'] }}" style="color: {{ $style['textColor'] }}; text-decoration: none;">{{ $sponsor['display_name'] }}</a>
                                @else
                                {{ $sponsor['display_name'] }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if (!empty($sponsor['tier']))
                        <tr>
                            <td align="center" style="padding-top: 2px;">
                                <span style="display: inline-block; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 10px; padding: 2px 6px; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 3px;' : '' }} {{ $sponsor['tier'] === 'gold' ? 'background-color: #fef3c7; color: #92400e;' : ($sponsor['tier'] === 'silver' ? 'background-color: #e5e7eb; color: #374151;' : 'background-color: #ffedd5; color: #9a3412;') }}">{{ __('messages.' . $sponsor['tier']) }}</span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
                @if (($index + 1) % 4 === 0 && $index + 1 < $sponsorCount)
                </tr><tr>
                @endif
                @endforeach
            </tr>
        </table>
    </td>
</tr>
@endif
