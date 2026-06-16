{{-- Global, operator-configured custom footer code (e.g. analytics, chat widgets).
     Set in /admin → Settings. Rendered raw before </body> on public guest pages,
     outside Vue's #app mount so scripts execute; the request CSP nonce is injected. --}}
@php $footerCode = \App\Models\Setting::get('custom_footer_code'); @endphp
@if (! empty($footerCode))
{!! inject_csp_nonce($footerCode) !!}
@endif
