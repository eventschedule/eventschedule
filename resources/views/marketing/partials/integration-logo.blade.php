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
        {{-- Invoice Ninja (official envelope-ninja mark; first subpath is the black tile,
             the face renders as winding holes over the white underlay rect) --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="7.5" width="18" height="9.2" fill="#fff"/>
            <path transform="translate(-0.48 -0.48) scale(1.04)" fill="#000" d="M7.193 2.385h9.615a4.808 4.808 0 014.807 4.808v9.614a4.808 4.808 0 01-4.807 4.808H7.193a4.808 4.808 0 01-4.808-4.808V7.193a4.808 4.808 0 014.808-4.808zM16.247 10.326a1.164 1.164 0 11-2.328 0 1.164 1.164 0 012.328 0zm-6.288 0a1.164 1.164 0 11-2.329 0 1.164 1.164 0 012.329 0zM16.459 14.531c-3.047-1.348-4.054-1.737-4.5-1.737-.446 0-1.433.38-4.38 1.684-2.091.926-3.828 1.76-3.86 1.79h16.663zm-9.873-.361c1.621-.729 3.06-1.387 3.196-1.464.258-.145.337-.09-5.285-3.682-.56-.358-1.023-.698-1.025-.65V15.564a790.1 790.1 0 003.114-1.394zm14.078-2.194V8.417c0-.11-1.676.993-3.496 2.12-3 1.854-3.281 2.06-3.004 2.185 1.345.611 6.42 2.862 6.5 2.872zm-8.169.11c.545.125.643.104 1.226-.263.349-.22.655-.419.681-.442.026-.024-.05-.181-.167-.35-.118-.168-.215-.5-.215-.739V9.86l-.569.21c-.726.267-2.28.27-3 .005l-.556-.205.013.452c.007.26-.088.563-.225.715-.232.256-.22.276.45.726.64.432.725.455 1.23.327a2.349 2.349 0 011.132-.002zm-4.23-2.65c-.105-.113-2.97-.954-3.033-.891-.03.03.504.414 1.186.854l1.24.8.34-.344c.186-.188.307-.377.268-.42zm9.76-.373c.473-.306.8-.555.728-.555-.155 0-2.877.804-3.027.894-.057.034.033.229.2.433l.304.37.47-.293c.257-.162.854-.544 1.326-.85zm-1.636-.555c2.11-.59 3.867-1.102 3.904-1.139H3.59c.187.187 7.779 2.195 8.323 2.202.41.005 2.014-.376 4.476-1.063z"/>
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
        {{-- Outlook (Fluent icon: O tile over envelope) --}}
        <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="2" y="2" width="20" height="20" rx="5" fill="#0F6CBD"/>
            <rect x="10.6" y="6.6" width="9.6" height="10.8" rx="1.2" fill="#fff"/>
            <path d="M10.6 8.8l4.8 3.2 4.8-3.1" stroke="#0F6CBD" stroke-width="1.3" fill="none" stroke-linejoin="round"/>
            <rect x="3.4" y="7.3" width="10" height="10" rx="1.6" fill="#0F6CBD"/>
            <circle cx="8.4" cy="12.3" r="3.1" fill="none" stroke="#fff" stroke-width="2.3"/>
        </svg>
        @break
@endswitch
