@php
    $blockEvents = $block['data']['resolvedEvents'] ?? collect();
    $layout = $block['data']['layout'] ?? $style['eventLayout'] ?? 'cards';
    $template = $template ?? 'modern';
@endphp
@if ($blockEvents->isNotEmpty())
<tr>
    <td style="background-color: {{ $template === 'bold' ? '#16213e' : ($template === 'classic' ? '#faf9f6' : '#ffffff') }}; padding: {{ $template === 'compact' ? '10px 30px' : '20px 30px' }};">
        <div style="margin-bottom: 10px;">
            @if ($layout === 'list')
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    @foreach ($blockEvents as $event)
                    <tr>
                        <td style="padding: {{ $template === 'compact' ? '8px 0' : '12px 0' }}; border-bottom: 1px solid {{ $template === 'bold' ? '#2a3a5c' : '#eee' }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width: {{ $template === 'compact' ? '100px' : '120px' }}; font-size: {{ $template === 'compact' ? '12px' : '13px' }}; color: {{ $template === 'bold' ? '#e0e0e0' : $style['accentColor'] }}; font-weight: bold; font-family: {{ $style['fontFamily'] }}, sans-serif; vertical-align: top; padding-right: 15px;">
                                        {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y') : '' }}
                                        @if ($event->starts_at)
                                        <br><span style="font-weight: normal; font-size: {{ $template === 'compact' ? '11px' : '12px' }}; color: {{ $template === 'bold' ? '#888' : '#999' }};">{{ \Carbon\Carbon::parse($event->starts_at)->format('g:i A') }}</span>
                                        @endif
                                    </td>
                                    <td style="font-size: {{ $template === 'compact' ? '14px' : '15px' }}; font-family: {{ $style['fontFamily'] }}, sans-serif; vertical-align: top;">
                                        @if ($template === 'minimal')
                                        <a href="{{ $role->getGuestUrl() }}" style="color: {{ $style['accentColor'] }}; text-decoration: underline; font-weight: bold;">{{ $event->name }}</a>
                                        @else
                                        <a href="{{ $role->getGuestUrl() }}" style="color: {{ $template === 'bold' ? '#ffffff' : $style['textColor'] }}; text-decoration: none; font-weight: bold;">{{ $event->name }}</a>
                                        @endif
                                        @php
                                            $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                                        @endphp
                                        @if ($venue)
                                        <br><span style="font-size: {{ $template === 'compact' ? '12px' : '13px' }}; color: {{ $template === 'bold' ? '#888' : '#888' }};">{{ $venue->name }}</span>
                                        @endif
                                    </td>
                                    @if ($template !== 'minimal')
                                    <td style="width: 60px; text-align: right; vertical-align: top;">
                                        <a href="{{ $role->getGuestUrl() }}" style="display: inline-block; padding: {{ $template === 'compact' ? '4px 10px' : '6px 12px' }}; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: {{ $template === 'compact' ? '11px' : '12px' }}; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 4px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ __('messages.view') }}</a>
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            @elseif ($template === 'minimal')
                {{-- Minimal cards: no image, thin border, text-link button --}}
                @foreach ($blockEvents as $event)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px; border-bottom: 1px solid #eee;">
                    <tr>
                        <td style="padding: 12px 0; vertical-align: top; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                            <h3 style="margin: 0 0 4px 0; font-size: 15px; color: {{ $style['textColor'] }}; font-weight: 600;">{{ $event->name }}</h3>
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: {{ $style['accentColor'] }};">
                                {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y - g:i A') : '' }}
                            </p>
                            @php
                                $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                            @endphp
                            @if ($venue)
                            <p style="margin: 0 0 6px 0; font-size: 13px; color: #888;">{{ $venue->name }}</p>
                            @endif
                            <a href="{{ $role->getGuestUrl() }}" style="color: {{ $style['accentColor'] }}; text-decoration: underline; font-size: 13px; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ __('messages.view_event') ?: 'View Event' }} &rarr;</a>
                        </td>
                    </tr>
                </table>
                @endforeach
            @elseif ($template === 'compact')
                {{-- Compact cards: smaller padding/fonts, no image, smaller buttons --}}
                @foreach ($blockEvents as $event)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px; border: 1px solid #eee;">
                    <tr>
                        <td style="padding: 8px 12px; vertical-align: top; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                            <h3 style="margin: 0 0 2px 0; font-size: 14px; color: {{ $style['textColor'] }};">{{ $event->name }}</h3>
                            <p style="margin: 0 0 2px 0; font-size: 11px; color: {{ $style['accentColor'] }}; font-weight: bold;">
                                {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y - g:i A') : '' }}
                            </p>
                            @php
                                $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                            @endphp
                            @if ($venue)
                            <p style="margin: 0 0 4px 0; font-size: 11px; color: #888;">{{ $venue->name }}</p>
                            @endif
                            <a href="{{ $role->getGuestUrl() }}" style="display: inline-block; padding: 4px 10px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 11px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 3px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ __('messages.view_event') ?: 'View Event' }}</a>
                        </td>
                    </tr>
                </table>
                @endforeach
            @else
                {{-- Cards layout for modern, classic, bold --}}
                @foreach ($blockEvents as $event)
                @php
                    $cardBorder = '1px solid #eee';
                    $cardBg = '';
                    $cardExtra = $template === 'modern' ? 'box-shadow: 0 1px 3px rgba(0,0,0,0.08);' : '';
                    $titleColor = $style['textColor'];
                    $btnStyle = "background-color: {$style['accentColor']}; color: #ffffff;";
                    if ($template === 'classic') {
                        $cardBorder = '1px solid #e0ddd5';
                        $cardBg = 'background-color: #faf9f6;';
                        $cardExtra = '';
                        $btnStyle = "background-color: transparent; color: {$style['accentColor']}; border: 2px solid {$style['accentColor']};";
                    } elseif ($template === 'bold') {
                        $cardBorder = 'none';
                        $cardBg = 'background-color: #1e2d50;';
                        $cardExtra = 'border-left: 4px solid ' . $style['accentColor'] . ';';
                        $titleColor = '#ffffff';
                    }
                @endphp
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px; border: {{ $cardBorder }}; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }} overflow: hidden; {{ $cardBg }} {{ $cardExtra }}">
                    <tr>
                        @if ($event->getImageUrl() && $template !== 'classic')
                        <td style="width: 120px; vertical-align: top;">
                            <img src="{{ $event->getImageUrl() }}" alt="{{ $event->name }}" width="120" style="width: 120px; height: auto; display: block; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px 0 0 8px;' : '' }}" />
                        </td>
                        @endif
                        <td style="padding: 14px 16px; vertical-align: top; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                            <h3 style="margin: 0 0 6px 0; font-size: 16px; color: {{ $titleColor }}; {{ $template === 'classic' ? 'font-family: Georgia, serif;' : '' }}">{{ $event->name }}</h3>
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: {{ $template === 'bold' ? '#e0e0e0' : $style['accentColor'] }}; font-weight: bold;">
                                {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y - g:i A') : '' }}
                            </p>
                            @php
                                $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                            @endphp
                            @if ($venue)
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #888;">{{ $venue->name }}</p>
                            @endif
                            <a href="{{ $role->getGuestUrl() }}" style="display: inline-block; padding: 8px 16px; {{ $btnStyle }} text-decoration: none; font-size: 13px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 4px;' : '' }}">{{ __('messages.view_event') ?: 'View Event' }}</a>
                        </td>
                    </tr>
                </table>
                @endforeach
            @endif
        </div>
    </td>
</tr>
@endif
