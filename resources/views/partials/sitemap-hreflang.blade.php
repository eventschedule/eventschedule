{{-- Emits xhtml:link alternate rows for a sitemap <url> entry. --}}
{{-- Usage: @include('partials.sitemap-hreflang', ['url' => url('/pricing')]) --}}
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $url }}"/>
        <xhtml:link rel="alternate" hreflang="en" href="{{ $url }}"/>
        @foreach (array_keys(config('app.supported_languages')) as $supportedLang)
            @if ($supportedLang !== 'en')
        <xhtml:link rel="alternate" hreflang="{{ $supportedLang }}" href="{{ $url }}?lang={{ $supportedLang }}"/>
            @endif
        @endforeach
