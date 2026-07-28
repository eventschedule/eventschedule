{{--
    A documentation icon by manifest key. Artwork lives in App\Utils\DocIcons
    so the hero chip, the index cards, the left rail and the search sprite all
    draw from one map.
--}}
@props(['name', 'class' => 'w-5 h-5', 'stroke' => '1.8'])

<svg {{ $attributes->merge(['class' => $class]) }}
     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $stroke }}"
     aria-hidden="true">
    @foreach (\App\Utils\DocIcons::paths($name) as $d)
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
    @endforeach
</svg>
