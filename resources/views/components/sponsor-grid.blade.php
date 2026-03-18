@props(['sponsors', 'title', 'maxWidth' => '200rem'])

@if (!empty($sponsors))
<div
    class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl px-6 lg:px-16 py-6 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto"
    data-view-width
    style="max-width: {{ $maxWidth }}"
>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center mb-6">
        {{ $title }}
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-8 place-content-center">
        @foreach ($sponsors as $sponsor)
            @php
                $sizeStyles = 'max-height: 5rem; max-width: 160px;';
            @endphp
            <div class="flex flex-col items-center text-center">
                @if (!empty($sponsor['url']))
                    <a href="{{ $sponsor['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="flex flex-col items-center text-center w-full hover:opacity-80 transition-opacity">
                @endif
                <div class="h-24 w-full flex items-end justify-center pb-2 overflow-hidden">
                    @if (!empty($sponsor['logo_url']))
                        <img src="{{ $sponsor['logo_url'] }}"
                            alt="{{ $sponsor['display_name'] }}"
                            style="{{ $sizeStyles }}"
                            class="object-contain"
                            loading="lazy" />
                    @endif
                </div>
                @if (!empty($sponsor['display_name']))
                    <span class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-full">{{ $sponsor['display_name'] }}</span>
                @endif
                @if (!empty($sponsor['tier']))
                    <span class="inline-block text-xs px-1.5 py-0.5 rounded mt-0.5
                        {{ $sponsor['tier'] === 'gold' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                        {{ $sponsor['tier'] === 'silver' ? 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' : '' }}
                        {{ $sponsor['tier'] === 'bronze' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : '' }}
                    ">{{ __('messages.' . $sponsor['tier']) }}</span>
                @endif
                @if (!empty($sponsor['url']))
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
