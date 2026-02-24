@props(['id', 'alt', 'loading' => 'lazy'])

<div class="my-6 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-sm">
    <picture class="block dark:hidden">
        <source srcset="{{ url("images/docs/{$id}.webp") }}" type="image/webp">
        <img src="{{ url("images/docs/{$id}.png") }}" alt="{{ $alt }}" class="w-full h-auto" loading="{{ $loading }}">
    </picture>
    <picture class="hidden dark:block">
        <source srcset="{{ url("images/docs/{$id}-dark.webp") }}" type="image/webp">
        <img src="{{ url("images/docs/{$id}-dark.png") }}" alt="{{ $alt }}" class="w-full h-auto" loading="{{ $loading }}">
    </picture>
</div>
