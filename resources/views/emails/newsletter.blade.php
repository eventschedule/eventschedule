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

                    @foreach ($blocks as $block)
                        @php $blockType = $block['type'] ?? ''; @endphp
                        @if (view()->exists('emails.newsletter_blocks._' . $blockType))
                            @include('emails.newsletter_blocks._' . $blockType, ['block' => $block, 'style' => $style, 'role' => $role])
                        @endif
                    @endforeach

                    <tr>
                        <td style="background-color: #f5f5f5; padding: 20px 30px; text-align: center; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 0 0 8px 8px;' : '' }} {{ ($newsletter->template ?? '') === 'classic' ? 'border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;' : '' }}">
                                <p style="margin: 0 0 8px 0; font-size: 13px; color: #999; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                                    {{ $role->name }}
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #bbb; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                                    <a href="{{ $unsubscribeUrl }}" style="color: #999; text-decoration: underline;">{{ __('messages.unsubscribe') }}</a>
                                </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
