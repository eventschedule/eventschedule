{{-- Global, operator-configured custom header code (e.g. Google Tag Manager).
     Set in /admin → Settings. Rendered raw inside <head> on public guest pages;
     the request CSP nonce is injected into <script> tags so they execute. --}}
@php $headerCode = \App\Models\Setting::get('custom_header_code'); @endphp
@if (! empty($headerCode))
{!! inject_csp_nonce($headerCode) !!}
@endif
