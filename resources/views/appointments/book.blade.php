<x-app-guest-layout :role="$role">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold mb-1">{{ $role->customLabel('book_a_time') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('messages.appointments_with', ['schedule' => $role->name]) }}</p>

        <div class="space-y-4">
            @foreach ($types as $type)
                <a href="{{ route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]) }}"
                   class="block p-5 rounded-xl border border-gray-200 dark:border-[#2d2d30] hover:shadow-md transition-all duration-200">
                    <div class="font-semibold text-lg">{{ $type->name }}</div>
                    @if ($type->description)
                        <div class="text-gray-500 mt-1">{{ $type->description }}</div>
                    @endif
                    <div class="text-sm text-gray-400 mt-2">
                        {{ $type->duration_minutes }} {{ __('messages.minutes') }}
                        &middot; {{ $type->isFree() ? __('messages.free') : strtoupper($type->currency_code).' '.number_format((float) $type->price, 2) }}
                        @if ($type->requires_approval)
                            &middot; {{ __('messages.appointments_requires_confirmation') }}
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-guest-layout>
