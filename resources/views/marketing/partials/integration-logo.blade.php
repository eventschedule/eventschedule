{{-- Shared brand marks for the "Integrates with" section. Rendered in both the
     desktop orbit and the mobile flat row, so it lives in one place. Each mark is
     a flat app-tile glyph on a 0 0 24 24 grid; size + hover treatment come from
     the passed $class. Keeps real brand colours (an established exemption from the
     blue-only rule for third-party logos). --}}
@php $iconClass = $class ?? 'h-9 w-9'; @endphp
@switch($name)
    @case('google')
        {{-- Google Calendar --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="4" fill="#fff" stroke="#E4E7EC" stroke-width="1"/>
            <path d="M18 3h-1.5v3.5H21V6a3 3 0 0 0-3-3z" fill="#34A853"/>
            <path d="M21 16.5H16.5V21H18a3 3 0 0 0 3-3v-1.5z" fill="#EA4335"/>
            <path d="M7.5 21v-4.5H3V18a3 3 0 0 0 3 3h1.5z" fill="#FBBC04"/>
            <path d="M3 7.5h4.5V3H6a3 3 0 0 0-3 3v1.5z" fill="#4285F4"/>
            <path d="M7.5 3v4.5H3v9h4.5V21h9v-4.5H21v-9h-4.5V3h-9z" fill="#fff"/>
            <text x="12" y="15.3" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="7.5" font-weight="700" fill="#4285F4">31</text>
        </svg>
        @break

    @case('stripe')
        {{-- Stripe --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="2" y="2" width="20" height="20" rx="5" fill="#635BFF"/>
            <path d="M11.28 9.65c0-.62.51-.86 1.34-.86 1.2 0 2.71.36 3.91 1V6.1c-1.31-.52-2.6-.72-3.91-.72-3.2 0-5.33 1.67-5.33 4.46 0 4.35 5.99 3.65 5.99 5.53 0 .73-.64.97-1.52.97-1.31 0-2.98-.54-4.3-1.26v3.75c1.46.63 2.94.9 4.3.9 3.28 0 5.53-1.62 5.53-4.45-.01-4.69-6.01-3.85-6.01-5.63z" fill="#fff"/>
        </svg>
        @break

    @case('invoiceninja')
        {{-- Invoice Ninja (ninja mask) --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="2" y="2" width="20" height="20" rx="5" fill="#1F292E"/>
            <path d="M3.8 10.4c3.2-1.5 13.2-1.5 16.4 0v2.6c-3.2 1.6-13.2 1.6-16.4 0v-2.6z" fill="#fff"/>
            <path d="M7 11.5c1.3-.5 2.9-.5 4 .1l-.4 1.5c-1-.5-2.1-.5-3.1-.1L7 11.5z" fill="#1F292E"/>
            <path d="M13 11.6c1.1-.6 2.7-.6 4-.1l-.5 1.5c-1-.4-2.1-.4-3.1.1l-.4-1.5z" fill="#1F292E"/>
        </svg>
        @break

    @case('caldav')
        {{-- CalDAV (generic calendar sync) --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="4" width="18" height="17" rx="4" fill="#0D9488"/>
            <path d="M3 8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v.5H3V8z" fill="#0F766E"/>
            <rect x="7" y="2.5" width="2" height="4" rx="1" fill="#fff"/>
            <rect x="15" y="2.5" width="2" height="4" rx="1" fill="#fff"/>
            <path d="M8.2 14.3l2.3 2.3 5-5.1" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('apple')
        {{-- Apple Calendar --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="4" fill="#fff" stroke="#E4E7EC" stroke-width="1"/>
            <path d="M3 7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v1.2H3V7z" fill="#FF3B30"/>
            <text x="12" y="17.6" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#1F2937">31</text>
        </svg>
        @break

    @case('outlook')
        {{-- Outlook --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="2" y="2" width="20" height="20" rx="5" fill="#0F6CBD"/>
            <rect x="12.5" y="7.5" width="7.5" height="9" rx="1" fill="#fff"/>
            <path d="M12.5 9l3.75 2.6L20 9.1" stroke="#0F6CBD" stroke-width="1.2" fill="none" stroke-linejoin="round"/>
            <ellipse cx="9" cy="12" rx="3.4" ry="4.1" fill="#0F6CBD" stroke="#fff" stroke-width="2.1"/>
        </svg>
        @break
@endswitch
