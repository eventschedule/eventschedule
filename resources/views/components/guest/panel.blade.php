{{-- A panel of the guest kit: the translucent card a section of a guest page sits in.

     It keeps the utility classes the panels have always carried beside the kit's own, because an
     owner's custom CSS may be written against them. --}}
@props([
    'as' => 'div',
    'pad' => true,
])
<{{ $as }} {{ $attributes->merge(['class' => 'gk-panel bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm'.($pad ? ' gk-pad' : '')]) }}>{{ $slot }}</{{ $as }}>
