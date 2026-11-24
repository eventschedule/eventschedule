@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'ap-btn ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-[rgb(var(--ap-border-strong))] dark:border-gray-600 rounded-xl font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none']) }}>
    {{ $slot }}
</a>
