@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800']) }}>
    {{ $slot }}
</a>
