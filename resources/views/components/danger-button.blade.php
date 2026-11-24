<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ap-btn inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-semibold text-base text-white hover:bg-red-700 active:bg-red-700 focus:outline-none transition-all duration-200 hover:shadow-md']) }}>
    {{ $slot }}
</button>
