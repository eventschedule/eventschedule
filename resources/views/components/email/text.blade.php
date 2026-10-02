{{--
    A paragraph of body copy. Variants: body (16/26), lead (18/28), small (14/22) and muted
    (14/22, lighter). No text-align unless asked: dir on the surrounding tables already starts the
    line on the correct side.
--}}
@aware(['theme' => null])
@props(['variant' => 'body', 'align' => null, 'gap' => 16])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    [$size, $line, $colour, $class] = match ($variant) {
        'lead' => [18, 28, '#334155', 'es-ink-2'],
        'small' => [14, 22, '#475569', 'es-ink-2'],
        'muted' => [14, 22, '#64748b', 'es-ink-3'],
        default => [16, 26, '#334155', 'es-ink-2'],
    };
    $textAlign = match ($align) {
        'center' => 'center',
        'end' => $theme->end,
        'start' => $theme->start,
        default => null,
    };
@endphp
<p class="{{ $class }}" style="margin: 0 0 {{ (int) $gap }}px; font-size: {{ $size }}px; line-height: {{ $line }}px; color: {{ $colour }};{{ $textAlign ? ' text-align: '.$textAlign.';' : '' }}">{{ $slot }}</p>
