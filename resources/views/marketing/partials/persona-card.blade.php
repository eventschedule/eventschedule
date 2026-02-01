@props(['persona'])

<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow text-center">
    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        @include('marketing.partials.icon', ['icon' => $persona['icon'], 'class' => 'w-8 h-8 text-white'])
    </div>
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $persona['title'] }}
    </h3>
    <p class="text-gray-600 dark:text-gray-400 text-sm">
        {{ $persona['description'] }}
    </p>
</div>
