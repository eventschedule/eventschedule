@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'ap-btn inline-flex items-center justify-center px-4 py-3 bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-xl font-semibold text-base text-white shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg-hover)] hover:to-[var(--brand-button-bg-hover)] hover:shadow-md focus:outline-none']) }}>
    {{ $slot }}
</a>
