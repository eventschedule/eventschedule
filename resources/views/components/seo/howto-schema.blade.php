@props(['name', 'description' => null, 'steps' => []])
@php
    $howToSteps = [];
    foreach (array_values($steps) as $index => $step) {
        if (!isset($step['name'], $step['text'])) {
            continue;
        }
        $howToSteps[] = [
            '@type' => 'HowToStep',
            'position' => $index + 1,
            'name' => (string) $step['name'],
            'text' => (string) $step['text'],
        ];
    }
    $howToPayload = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => $name,
        'description' => $description,
        'step' => $howToSteps,
    ], fn ($v) => $v !== null && $v !== '' && $v !== []);
@endphp
@if (!empty($howToSteps))
<script type="application/ld+json" {!! nonce_attr() !!}>
{!! json_encode($howToPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
