<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsletter->subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: {{ $style['fontFamily'] }}, sans-serif; background-color: {{ $style['backgroundColor'] }}; color: {{ $style['textColor'] }};">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: {{ $style['backgroundColor'] }};">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    @php $template = $newsletter->template ?? 'modern'; @endphp
                    @foreach ($blocks as $block)
                        @php $blockType = $block['type'] ?? ''; @endphp
                        @if (view()->exists('emails.newsletter_blocks._' . $blockType))
                            @include('emails.newsletter_blocks._' . $blockType, ['block' => $block, 'style' => $style, 'role' => $role, 'template' => $template])
                        @endif
                    @endforeach

                    @php
                        $footerBg = '#f5f5f5';
                        $footerTextColor = '#999';
                        $footerLinkColor = '#999';
                        $footerExtra = '';
                        if ($template === 'bold') {
                            $footerBg = '#0d1525';
                            $footerTextColor = '#aaa';
                            $footerLinkColor = '#aaa';
                        } elseif ($template === 'classic') {
                            $footerExtra = 'border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;';
                        }
                    @endphp
                    <tr>
                        <td style="background-color: {{ $footerBg }}; padding: {{ $template === 'compact' ? '14px 30px' : '20px 30px' }}; text-align: center; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 0 0 8px 8px;' : '' }} {{ $footerExtra }}">
                                <p style="margin: 0 0 8px 0; font-size: {{ $template === 'compact' ? '12px' : '13px' }}; color: {{ $footerTextColor }}; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                                    {{ $role->name }}
                                </p>
                                <p style="margin: 0; font-size: {{ $template === 'compact' ? '11px' : '12px' }}; color: {{ $footerTextColor }}; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                                    <a href="{{ $unsubscribeUrl }}" style="color: {{ $footerLinkColor }}; text-decoration: underline;">{{ __('messages.unsubscribe') }}</a>
                                </p>
                                @if (!empty($showBranding))
                                <p style="margin: 10px 0 0 0; font-size: {{ $template === 'compact' ? '10px' : '11px' }}; color: {{ $footerTextColor }}; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                                    <a href="https://eventschedule.com" style="color: {{ $footerLinkColor }}; text-decoration: none;">{{ __('messages.powered_by_event_schedule') }}</a>
                                </p>
                                @endif
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
