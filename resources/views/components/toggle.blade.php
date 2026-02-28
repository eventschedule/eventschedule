<div class="flex items-center gap-3">
    <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
            {{ $checked ? 'checked' : '' }}
            {{ $attributes }}
            class="sr-only peer">
        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[#4E81FA] transition-colors"></div>
        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
    </label>
    <label for="{{ $id }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">{!! $label !!}</label>
</div>
@if ($help)
<p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ms-14">{!! $help !!}</p>
@endif
