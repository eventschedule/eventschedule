{{--
    A YouTube video or a Google Maps embed that is not loaded until the visitor wants it: with the
    marketing cookie category granted, or after a click on the placeholder for this one item
    (resources/js/components/ConsentEmbed.vue). Before that nothing is requested from Google, which
    would otherwise see every visitor's IP address and could set cookies.

    Every guest-page YouTube or Maps iframe goes through this component, never a bare <iframe>:
    ConsentEmbedTest fails the build otherwise.

    The host is EMPTY and every value reaches Vue as JSON in the attribute, so user-controlled text
    (an event or venue name in the title) is rendered by Vue as a prop and never compiled as a
    template. Escaped by {{ }} like any attribute value, and attribute values are not compiled.

    kind: 'video' or 'map'. poster: an image shown behind the button, which must come from this
    install (the YouTube thumbnail proxy, the static map proxy), never from Google.
--}}
@props([
    'src' => null,
    'title' => '',
    'kind' => 'video',
    'frameClass' => 'w-full',
    'frameStyle' => '',
    'poster' => null,
])

@if ($src)
    @php
        $consentEmbedProps = [
            'src' => $src,
            'title' => (string) $title,
            'kind' => $kind,
            'frameClass' => $frameClass,
            'frameStyle' => $frameStyle,
            'allow' => $kind === 'video' ? 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' : '',
            'referrerpolicy' => $kind === 'video' ? 'strict-origin-when-cross-origin' : 'no-referrer-when-downgrade',
            'poster' => (string) ($poster ?? ''),
            'button' => __($kind === 'video' ? 'messages.consent_embed_video_button' : 'messages.consent_embed_map_button'),
            'body' => __($kind === 'video' ? 'messages.consent_embed_video_body' : 'messages.consent_embed_map_body'),
        ];
    @endphp
    <div data-consent-embed="{{ json_encode($consentEmbedProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" class="{{ $frameClass }}" style="{{ $frameStyle }}"></div>
@endif
