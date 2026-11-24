<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ap-btn inline-flex items-center justify-center px-6 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-xl font-semibold text-base text-white hover:bg-[var(--brand-button-bg-hover)] active:bg-[var(--brand-button-bg-hover)] focus:outline-none transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:shadow-none']) }}>
    {{ $slot }}
</button>
