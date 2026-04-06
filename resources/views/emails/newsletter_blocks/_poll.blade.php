@php
    $poll = $block['data']['resolvedPoll'] ?? null;
    $template = $template ?? 'modern';
@endphp
@if ($poll)
<tr>
    <td style="padding: 20px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid {{ $style['accentColor'] }}33; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }}">
            <tr>
                <td style="padding: 20px 24px 8px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 18px; font-weight: bold; color: {{ $style['textColor'] }};">
                    {{ $poll['question'] }}
                </td>
            </tr>
            @if (!empty($poll['eventName']))
            <tr>
                <td style="padding: 0 24px 12px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 12px; color: {{ $style['textColor'] }}99;">
                    {{ $poll['eventName'] }}
                </td>
            </tr>
            @endif
            @foreach ($poll['options'] as $option)
            <tr>
                <td style="padding: 4px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: {{ $style['accentColor'] }}0d; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }}">
                        <tr>
                            <td style="padding: 10px 14px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 14px; color: {{ $style['textColor'] }};">
                                {{ $option }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endforeach
            <tr>
                <td align="center" style="padding: 16px 24px 20px;">
                    <a href="{{ $poll['eventUrl'] }}" style="display: inline-block; padding: 12px 24px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ __('messages.vote_now') }}</a>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endif
