@props(['feature'])

<div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-6">
        @include('marketing.partials.icon', ['icon' => $feature['icon'], 'class' => 'w-6 h-6 text-blue-600 dark:text-blue-400'])
    </div>
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
        {{ $feature['title'] }}
    </h3>
    <p class="text-gray-600 dark:text-gray-400 mb-4">
        {{ $feature['description'] }}
    </p>
    @if(isset($feature['details']))
    <ul class="space-y-2">
        @foreach($feature['details'] as $detail)
        <li class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ $detail }}
        </li>
        @endforeach
    </ul>
    @endif
</div>
