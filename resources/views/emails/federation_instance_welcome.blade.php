@php
    // Built on the house inline-style shell (subscription_confirmation.blade.php), with tables for
    // the step layout because that is what every mail client lays out the same way.
    $align = $isRtl ? 'right' : 'left';
    $textPadding = $isRtl ? 'padding-right' : 'padding-left';
    // Repeated on the layout tables: Gmail strips dir from <html>, and the step tables then lay
    // their number column out on the wrong side.
    $dir = $isRtl ? 'rtl' : 'ltr';

    $preheader = match ($state) {
        'live' => __('messages.federation_welcome_preheader_live'),
        'received' => __('messages.federation_welcome_preheader_received'),
        default => __('messages.federation_welcome_preheader_empty'),
    };

    $statusTitle = match ($state) {
        'live' => trans_choice('messages.federation_welcome_live_title', $liveCount, ['count' => number_format($liveCount)]),
        'received' => trans_choice('messages.federation_welcome_received_title', $receivedCount, ['count' => number_format($receivedCount)]),
        default => __('messages.federation_welcome_empty_title'),
    };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Declared, so clients that support dark mode apply the overrides below instead of
         inverting the whole message, gradient header and code block included. --}}
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ __('messages.federation_welcome_subject') }}</title>
    <style>
        @media (prefers-color-scheme: dark) {
            .es-page { background-color: #0f172a !important; }
            .es-card { background-color: #1e293b !important; }
            .es-ink { color: #f1f5f9 !important; }
            .es-ink-2 { color: #cbd5e1 !important; }
            .es-ink-3 { color: #94a3b8 !important; }
            .es-panel { background-color: #172554 !important; border-color: #1e3a8a !important; }
            .es-panel-title { color: #bfdbfe !important; }
            .es-number { background-color: #1e3a8a !important; color: #dbeafe !important; }
            .es-code { background-color: #0f172a !important; border-color: #334155 !important; color: #e2e8f0 !important; }
            .es-tip { background-color: #0f172a !important; border-color: #334155 !important; }
            .es-link { color: #93c5fd !important; }
            .es-rule { border-color: #334155 !important; }
        }
        @media only screen and (max-width: 480px) {
            .es-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .es-secondary { display: block !important; margin: 0 0 10px !important; }
            .es-dot { display: none !important; }
        }
    </style>
</head>
<body class="es-page" style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b;">
    {{-- Preheader: the line clients show next to the subject. --}}
    <div style="display: none; max-height: 0; max-width: 0; overflow: hidden; opacity: 0; mso-hide: all;">{{ $preheader }}</div>

    <table role="presentation" dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-page" style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 24px 12px;">
                <table role="presentation" dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px;">

                    {{-- Header. The first two stops of the brand gradient: the third, #22D3EE, is too
                         light for white text. Clients that drop background-image (Outlook) keep
                         the solid blue underneath. --}}
                    <tr>
                        <td class="es-pad" style="background-color: #4E81FA; background-image: linear-gradient(135deg, #4E81FA 0%, #0EA5E9 100%); border-radius: 14px 14px 0 0; padding: 36px 32px; text-align: center;">
                            <p style="margin: 0 0 10px; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #ffffff;">{{ config('app.name') }}</p>
                            <h1 style="margin: 0; font-size: 26px; line-height: 1.25; font-weight: 700; color: #ffffff;">{{ __('messages.federation_welcome_heading') }}</h1>
                            <p style="margin: 10px 0 0; font-size: 16px; color: #ffffff;">{{ $bold('messages.federation_welcome_subheading', ['host' => $host], ['host']) }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="es-card es-pad" style="background-color: #ffffff; border-radius: 0 0 14px 14px; padding: 32px; text-align: {{ $align }};">
                            <p class="es-ink" style="margin: 0 0 24px; font-size: 16px; color: #1e293b;">{{ __('messages.federation_welcome_intro') }}</p>

                            {{-- Where the install stands, with the one action that matters now
                                 directly under it, so nobody scrolls past three steps to find it. --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 22px 24px; text-align: {{ $align }};">
                                        <p class="es-panel-title" style="margin: 0; font-size: 18px; line-height: 1.35; font-weight: 700; color: #1e3a8a;">{{ $statusTitle }}</p>

                                        @if ($state === 'empty')
                                            <p class="es-ink-2" style="margin: 8px 0 0; font-size: 15px; color: #334155;">{{ $bold('messages.federation_welcome_empty_body', $labels, ['undecided', 'option']) }}</p>
                                        @endif

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top: 18px;">
                                            <tr>
                                                <td style="border-radius: 8px; background-color: #4E81FA;">
                                                    <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" style="display: inline-block; padding: 13px 26px; font-size: 16px; font-weight: 600; line-height: 1.2; color: #ffffff; text-decoration: none; border-radius: 8px;">{{ $ctaLabel }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <h2 class="es-ink" style="margin: 34px 0 18px; font-size: 19px; line-height: 1.3; font-weight: 700; color: #0f172a;">{{ __('messages.federation_welcome_steps_title') }}</h2>

                            {{-- 1. List your schedules --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 22px;">
                                <tr>
                                    <td width="32" valign="top" style="width: 32px; vertical-align: top;">
                                        <div class="es-number" style="width: 30px; height: 30px; line-height: 30px; border-radius: 15px; background-color: #dbeafe; color: #1d4ed8; font-size: 14px; font-weight: 700; text-align: center;">1</div>
                                    </td>
                                    <td valign="top" style="vertical-align: top; {{ $textPadding }}: 14px; text-align: {{ $align }};">
                                        <p class="es-ink" style="margin: 3px 0 4px; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('messages.federation_welcome_step_list_title') }}</p>
                                        <p class="es-ink-2" style="margin: 0; font-size: 15px; color: #475569;">
                                            @if ($oneClick)
                                                {{ $bold('messages.federation_welcome_step_list_body_oneclick', $labels + ['host' => $host], ['host', 'section']) }}
                                            @else
                                                {{ $bold('messages.federation_welcome_step_list_body_manual', $labels + ['host' => $host], ['host', 'setting', 'option']) }}
                                            @endif
                                        </p>

                                        @if ($showUpdateTip)
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-tip" style="margin-top: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                <tr>
                                                    <td class="es-ink-2" style="padding: 10px 14px; font-size: 14px; color: #475569; text-align: {{ $align }};">
                                                        {{ $bold('messages.federation_welcome_step_update_tip', ['version' => $installedVersion, 'required' => $requiredVersion], ['required']) }}
                                                        <a href="{{ $updateGuideUrl }}" target="_blank" rel="noopener" class="es-link" style="color: #2563eb; text-decoration: underline;">{{ __('messages.federation_welcome_update_link') }}</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- 2. What qualifies --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 22px;">
                                <tr>
                                    <td width="32" valign="top" style="width: 32px; vertical-align: top;">
                                        <div class="es-number" style="width: 30px; height: 30px; line-height: 30px; border-radius: 15px; background-color: #dbeafe; color: #1d4ed8; font-size: 14px; font-weight: 700; text-align: center;">2</div>
                                    </td>
                                    <td valign="top" style="vertical-align: top; {{ $textPadding }}: 14px; text-align: {{ $align }};">
                                        <p class="es-ink" style="margin: 3px 0 4px; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('messages.federation_welcome_step_qualify_title') }}</p>
                                        <p class="es-ink-2" style="margin: 0; font-size: 15px; color: #475569;">{{ __('messages.federation_welcome_step_qualify_body') }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- 3. Sync --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 8px;">
                                <tr>
                                    <td width="32" valign="top" style="width: 32px; vertical-align: top;">
                                        <div class="es-number" style="width: 30px; height: 30px; line-height: 30px; border-radius: 15px; background-color: #dbeafe; color: #1d4ed8; font-size: 14px; font-weight: 700; text-align: center;">3</div>
                                    </td>
                                    <td valign="top" style="vertical-align: top; {{ $textPadding }}: 14px; text-align: {{ $align }};">
                                        <p class="es-ink" style="margin: 3px 0 4px; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('messages.federation_welcome_step_sync_title') }}</p>
                                        <p class="es-ink-2" style="margin: 0; font-size: 15px; color: #475569;">{{ __('messages.federation_welcome_step_sync_body') }}</p>
                                        {{-- A command, so always left-to-right, even in an RTL message. --}}
                                        <p dir="ltr" class="es-code" style="margin: 10px 0 0; padding: 10px 14px; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; font-family: SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace; font-size: 14px; color: #111827; text-align: left;">php artisan federation:push</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 28px 0 0; font-size: 15px; text-align: {{ $align }};">
                                @if ($state !== 'empty')
                                    <a href="{{ $guideUrl }}" target="_blank" rel="noopener" class="es-link es-secondary" style="color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('messages.federation_welcome_guide') }}</a>
                                    <span class="es-ink-3 es-dot" style="color: #94a3b8;">&nbsp;&middot;&nbsp;</span>
                                @endif
                                <a href="{{ $browseUrl }}" target="_blank" rel="noopener" class="es-link es-secondary" style="color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('messages.federation_welcome_browse') }}</a>
                            </p>

                            <p class="es-ink-2 es-rule" style="margin: 28px 0 0; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 15px; color: #475569;">{{ __('messages.federation_welcome_reply') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="es-pad" style="padding: 18px 32px 0; text-align: center;">
                            <p class="es-ink-3" style="margin: 0; font-size: 12px; color: #94a3b8;">{{ $bold('messages.federation_welcome_why', ['host' => $host], ['host']) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
