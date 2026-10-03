{{-- window.esConsent: the visitor's cookie choice, readable synchronously. Included in <head>
     by every shell that can run a consent-gated script (layouts/app, auth, legal, marketing),
     BEFORE partials.google-analytics, which reads it inline. The source is
     resources/js/consent-state.js, which cookie-consent.js also imports, so the parser exists
     once. It reads only the browser's own storage, so it is safe on an edge-cached page. --}}
<script {!! nonce_attr() !!}>{!! consent_state_script() !!}</script>
