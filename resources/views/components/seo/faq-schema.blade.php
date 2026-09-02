@props(['items' => []])
@php
    $faqEntities = array_values(array_filter(array_map(function ($item) {
        if (!isset($item['q'], $item['a'])) {
            return null;
        }
        return [
            '@type' => 'Question',
            'name' => (string) $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => (string) $item['a'],
            ],
        ];
    }, $items)));
    $faqPayload = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
@endphp
@if (!empty($faqEntities))
<script type="application/ld+json" {!! nonce_attr() !!}>
{!! \App\Utils\SeoUtils::jsonLd($faqPayload) !!}
</script>
@endif
