{{-- A template's design at a glance: the colours it was saved with, drawn as a small letter (a
     heading, two lines, a button). The colours are the EMAIL's own, saved with the template, which
     is why they are not palette tokens; a value that is not a plain hex colour falls back to the
     design's default rather than reaching a style attribute.

     Expects $swatchOf: anything with ->template and ->style_settings. --}}
@php
    $swatchDefaults = \App\Models\Newsletter::templateDefaults((string) ($swatchOf->template ?? 'modern'));
    $swatchSaved = is_array($swatchOf->style_settings ?? null) ? $swatchOf->style_settings : [];
    $swatchColor = function (string $key) use ($swatchSaved, $swatchDefaults) {
        $value = $swatchSaved[$key] ?? null;

        return is_string($value) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : $swatchDefaults[$key];
    };
    $swatchSquare = ($swatchSaved['buttonRadius'] ?? $swatchDefaults['buttonRadius']) === 'square';
@endphp
<span class="news-swatch" style="background: {{ $swatchColor('backgroundColor') }}" aria-hidden="true">
    <i class="news-swatch-bar" style="background: {{ $swatchColor('accentColor') }}"></i>
    <i class="news-swatch-line" style="background: {{ $swatchColor('textColor') }}"></i>
    <i class="news-swatch-line is-short" style="background: {{ $swatchColor('textColor') }}"></i>
    <i class="news-swatch-button{{ $swatchSquare ? ' is-square' : '' }}" style="background: {{ $swatchColor('accentColor') }}"></i>
</span>
