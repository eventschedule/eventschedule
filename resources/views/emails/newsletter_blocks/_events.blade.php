@php
    $blockEvents = $block['data']['resolvedEvents'] ?? collect();
    $layout = $block['data']['layout'] ?? $style['eventLayout'] ?? 'cards';
@endphp
@if ($blockEvents->isNotEmpty())
<tr>
    <td style="background-color: #ffffff; padding: 20px 30px;">
        <div style="margin-bottom: 10px;">
            @if ($layout === 'list')
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    @foreach ($blockEvents as $event)
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width: 120px; font-size: 13px; color: {{ $style['accentColor'] }}; font-weight: bold; font-family: {{ $style['fontFamily'] }}, sans-serif; vertical-align: top; padding-right: 15px;">
                                        {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y') : '' }}
                                        @if ($event->starts_at)
                                        <br><span style="font-weight: normal; font-size: 12px; color: #999;">{{ \Carbon\Carbon::parse($event->starts_at)->format('g:i A') }}</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 15px; font-family: {{ $style['fontFamily'] }}, sans-serif; vertical-align: top;">
                                        <a href="{{ $role->getGuestUrl() }}" style="color: {{ $style['textColor'] }}; text-decoration: none; font-weight: bold;">{{ $event->name }}</a>
                                        @php
                                            $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                                        @endphp
                                        @if ($venue)
                                        <br><span style="font-size: 13px; color: #888;">{{ $venue->name }}</span>
                                        @endif
                                    </td>
                                    <td style="width: 60px; text-align: right; vertical-align: top;">
                                        <a href="{{ $role->getGuestUrl() }}" style="display: inline-block; padding: 6px 12px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 12px; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 4px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ __('messages.view') }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            @else
                @foreach ($blockEvents as $event)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px; border: 1px solid #eee; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }} overflow: hidden;">
                    <tr>
                        @if ($event->getImageUrl())
                        <td style="width: 120px; vertical-align: top;">
                            <img src="{{ $event->getImageUrl() }}" alt="{{ $event->name }}" style="width: 120px; height: 120px; object-fit: cover; display: block; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px 0 0 8px;' : '' }}" />
                        </td>
                        @endif
                        <td style="padding: 14px 16px; vertical-align: top; font-family: {{ $style['fontFamily'] }}, sans-serif;">
                            <h3 style="margin: 0 0 6px 0; font-size: 16px; color: {{ $style['textColor'] }};">{{ $event->name }}</h3>
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: {{ $style['accentColor'] }}; font-weight: bold;">
                                {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y - g:i A') : '' }}
                            </p>
                            @php
                                $venue = $event->venue ?? ($event->roles ? $event->roles->where('type', 'venue')->first() : null);
                            @endphp
                            @if ($venue)
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #888;">{{ $venue->name }}</p>
                            @endif
                            <a href="{{ $role->getGuestUrl() }}" style="display: inline-block; padding: 8px 16px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 4px;' : '' }}">{{ __('messages.view_event') ?: 'View Event' }}</a>
                        </td>
                    </tr>
                </table>
                @endforeach
            @endif
        </div>
    </td>
</tr>
@endif
