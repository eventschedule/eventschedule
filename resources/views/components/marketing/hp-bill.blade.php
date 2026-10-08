{{--
    A list of names set like the foot of a festival bill (partials/hp-kit; the homepage's
    "everything else"). Each name is a link; its sentence stands under the bill while the name is
    pointed at or focused, and under the name itself where nothing can be pointed at.

    Props:
      items  [['href' => ..., 'title' => ..., 'desc' => ...], ...]
      left   the label at the top's leading edge
      right  the label at its trailing edge
      hint   the words in the sentence's place until a name is pointed at
    The slot is the heading. Three sizes of name: the first three, the next four, the rest.
--}}
@props([
    'items',
    'left' => 'Also on the bill',
    'right' => null,
    'hint' => 'Point at a name',
])
<div {{ $attributes->class(['hp-bill']) }} data-reveal="panel">
    <p class="hp-bill-top" aria-hidden="true"><span>{{ $left }}</span>@if ($right)<span>{{ $right }}</span>@endif</p>
    <h2 class="hp-h2">{{ $slot }}</h2>
    <ul class="hp-bill-list" data-hint="{{ $hint }}">
        @foreach ($items as $item)
            <li>
                <a href="{{ $item['href'] }}">
                    <span class="hp-bill-name">{{ $item['title'] }}</span>
                    <span class="hp-bill-desc">{{ $item['desc'] }}</span>
                </a>
            </li>
            @if (in_array($loop->index, [2, 6], true) && ! $loop->last)
                <li class="hp-bill-break" role="presentation"></li>
            @endif
        @endforeach
    </ul>
</div>
