@props(['plan'])

<div class="bg-white dark:bg-gray-800 rounded-2xl p-8 {{ $plan['highlighted'] ? 'ring-2 ring-blue-600 shadow-xl' : 'shadow-sm border border-gray-100 dark:border-gray-700' }} relative">
    @if($plan['highlighted'])
    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
        <span class="bg-blue-600 text-white text-sm font-medium px-4 py-1 rounded-full">
            Most Popular
        </span>
    </div>
    @endif

    <div class="text-center mb-8">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            {{ $plan['name'] }}
        </h3>
        <div class="flex items-baseline justify-center mb-2">
            <span class="text-5xl font-bold text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">{{ $plan['period'] }}</span>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            {{ $plan['description'] }}
        </p>
    </div>

    <ul class="space-y-4 mb-8">
        @foreach($plan['features'] as $feature)
        <li class="flex items-start">
            <svg class="w-5 h-5 mr-3 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-gray-600 dark:text-gray-300">{{ $feature }}</span>
        </li>
        @endforeach
    </ul>

    <a href="{{ route('sign_up') }}" class="block w-full text-center px-6 py-3 {{ $plan['highlighted'] ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white' }} font-medium rounded-xl transition-colors">
        {{ $plan['cta'] }}
    </a>
</div>
