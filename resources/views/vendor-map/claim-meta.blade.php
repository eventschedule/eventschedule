{{-- The head of a vendor's page: never indexed (the link is for the people it was sent to), and a
     share preview that is the market's own (its name, its map card) rather than ours. --}}
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="same-origin">
@if (! empty($ogTitle))
<meta property="og:title" content="{{ $ogTitle }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $role->translatedName() }}">
@endif
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
@if (! empty($ogImage))
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:card" content="summary_large_image">
@else
<meta name="twitter:card" content="summary">
@endif
